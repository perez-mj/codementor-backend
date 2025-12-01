<?php
class ExecController extends Controller {
    public function execute()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        $code = $input['code'] ?? '';

        if (!$code) {
            $this->api->respond_error('Missing "code"', 400);
            return;
        }

        // ⚠️ Allowlist dangerous functions to block
        $blocked = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 
                    'eval', 'create_function', 'assert', 'include', 'require', 
                    'unserialize', 'file_get_contents', 'fopen', 'curl_init'];

        foreach ($blocked as $func) {
            if (stripos($code, $func . '(') !== false) {
                $this->api->respond_error('Forbidden function: ' . $func, 403);
                return;
            }
        }

        // Capture output & errors
        ob_start();
        $error = null;
        $result = null;

        try {
            // Run user code in isolated scope
            $result = eval('?>' . $code);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $output = ob_get_clean();

        // Return structured response
        $payload = [
            'stdout' => $output,
            'return' => $result,
            'error'  => $error,
        ];

        if ($error) {
            $this->api->respond_error($payload, 500);
        } else {
            $this->api->respond($payload);
        }
    }
}