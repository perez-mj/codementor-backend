<?php

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Github;               // \u2705
use League\OAuth2\Client\Provider\GithubResourceOwner; // \u2705 optional, for type hinting
use League\OAuth2\Client\Token\AccessToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class OAuthService
{
    private string $jwtSecret;
    private string $frontendUrl;
    private array $googleConfig;
    private array $githubConfig;

    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'];
        $this->frontendUrl = $_ENV['FRONTEND_URL'];
        $this->googleConfig = [
            'clientId' => $_ENV['GOOGLE_CLIENT_ID'],
            'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
            'redirectUri' => $_ENV['API_URL'] . '/auth/google/callback',
        ];
        $this->githubConfig = [
            'clientId' => $_ENV['GITHUB_CLIENT_ID'],
            'clientSecret' => $_ENV['GITHUB_CLIENT_SECRET'],
            'redirectUri' => $_ENV['API_URL'] . '/auth/github/callback',
        ];
    }

    private function generateState(): string
    {
        $payload = [
            'nonce' => bin2hex(random_bytes(16)),
            'exp' => time() + 600, // 10 min expiry
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function validateState(string $state): bool
    {
        try {
            JWT::decode($state, new Key($this->jwtSecret, 'HS256'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getGoogleAuthUrl(): string
    {
        $provider = new Google($this->googleConfig);
        $state = $this->generateState();
        return $provider->getAuthorizationUrl(['state' => $state, 'scope' => ['openid', 'email', 'profile']]);
    }

    public function getGithubAuthUrl(): string
    {
        $provider = new Github($this->githubConfig);
        $state = $this->generateState();
        return $provider->getAuthorizationUrl([
            'state' => $state,
            'scope' => ['read:user', 'user:email'] // ✅ required for full profile

        ]);
    }

    public function handleGoogleCallback(string $code, string $state): ?array
    {
        if (!$this->validateState($state))
            return null;

        $provider = new Google($this->googleConfig);
        try {
            $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $user = $provider->getResourceOwner($token);
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'picture' => $user->getAvatar(),
                'email_verified' => $user->getEmailVerified() ?? true,
            ];
        } catch (\Exception $e) {
            error_log("Google OAuth error: " . $e->getMessage());
            return null;
        }
    }

    public function handleGithubCallback(string $code, string $state): ?array
    {
        if (!$this->validateState($state))
            return null;

        $provider = new Github($this->githubConfig);
        try {
            $token = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $user = $provider->getResourceOwner($token);

            // ✅ CORRECT way to fetch emails (no `request()` method needed)
            $request = $provider->getAuthenticatedRequest(
                'GET',
                'https://api.github.com/user/emails',
                $token
            );
            $response = $provider->getHttpClient()->send($request);
            $emails = json_decode((string) $response->getBody(), true);

            $picture = null;
            if (method_exists($user, 'getAvatarUrl')) {
                $picture = $user->getAvatarUrl();
            } else {
                $raw = $user->toArray();
                $picture = $raw['avatar_url'] ?? null;
            }

            $primary = null;
            foreach ($emails as $email) {
                if (!empty($email['primary']) && !empty($email['email'])) {
                    $primary = $email;
                    break;
                }
            }

            return [
                'id' => $user->getId(),
                'email' => $primary['email'] ?? $user->getEmail(),
                'name' => $user->getName() ?: $user->getNickname(),
                'picture' => $picture,
                'email_verified' => ($primary['verified'] ?? false),
            ];
        } catch (\Exception $e) {
            error_log("GitHub OAuth error: " . $e->getMessage());
            return null;
        }
    }
}