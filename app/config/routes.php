<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

// =============================================================================
// PUBLIC ROUTES - No authentication required
// =============================================================================

// Auth endpoints
$router->post('auth/login', 'ApiController::login');
$router->post('auth/register', 'ApiController::register');
$router->get('auth/verify-email', 'ApiController::verify_email');

// \u2705 OAuth: Redirect initiation (called by Vue \u2192 returns auth_url)
$router->get('auth/google', 'ApiController::googleLogin');
$router->get('auth/github', 'ApiController::githubLogin');

// \u2705 OAuth: Callback handlers (called by provider \u2192 sets cookies \u2192 redirects to frontend)
$router->get('auth/google/callback', 'ApiController::googleCallback');
$router->get('auth/github/callback', 'ApiController::githubCallback');

// \u2705 For frontend to hydrate user after OAuth (uses HTTP-only cookie)
$router->get('me', 'ApiController::getCurrentUser');  // \u2190 highly recommended

// Public content endpoints
$router->get('languages', 'ApiLanguageController::list');
$router->get('languages/{slug}', 'ApiLanguageController::get');
$router->get('languages/{lang_id}/lessons', 'ApiLessonsController::listByLanguage');
$router->get('languages/{lang_id}/lessons/{topic}', 'ApiLessonsController::getLesson');

$router->get('categories', 'CategoriesController::list');
$router->get('categories/{id}', 'CategoriesController::get');

$router->get('challenges', 'UserChallengeController::list');
$router->get('challenges/{id|slug}', 'UserChallengeController::get');

$router->get('achievements', 'AchievementsController::list');
$router->get('achievements/{id}', 'AchievementsController::get');


// =============================================================================
// USER ROUTES - Requires user authentication
// =============================================================================

// User profile & auth
$router->post('auth/logout', 'ApiController::logout');
$router->post('auth/refresh', 'ApiController::refresh');
$router->get('auth/profile', 'ApiController::profile');
$router->put('auth/update/{id}', 'ApiController::update');
// user schallenge submission
$router->post('challenges/{id}/submit', 'UserChallengeController::submit');
// In your routes file, change from:
$router->get('/submissions/{id}', 'SubmissionsController::get');

// To:
$router->get('/submissions/{id}', 'UserChallengeController::getSubmission');
// User data
$router->get('user_stats', 'UserStatsController::get_stats');
$router->put('user_stats', 'UserStatsController::update_stats');
$router->get('settings', 'UserSettingsController::get_all');
$router->put('settings', 'UserSettingsController::update');
$router->get('submissions', 'SubmissionsController::list');
$router->get('submissions/{id}', 'SubmissionsController::get');


// =============================================================================
// ADMIN ROUTES - Requires admin privileges
// =============================================================================

// Admin Management Routes
$router->get('admin/users', 'UsersController::list');
$router->get('admin/users/stats', 'UsersController::stats');
$router->get('admin/users/{id}', 'UsersController::get');
$router->put('admin/users/{id}/role', 'UsersController::updateRole');
$router->post('admin/users/{id}/moderate', 'UsersController::moderate');
$router->delete('delete/{id}', 'ApiController::delete');
$router->get('list', 'ApiController::list');

// Admin User Analytics
$router->get('admin/users/{id}/progress', 'UserProgressController::getProgress');
$router->get('admin/users/{id}/learning-paths', 'UserProgressController::getLearningPaths');
$router->get('admin/users/{id}/submissions', 'SubmissionsController::getUserSubmissions');
$router->get('admin/users/{id}/ai-interactions', 'AIInteractionsController::getUserInteractions');

// Admin Analytics & Stats
$router->get('admin/stats', 'AdminStatsController::stats');
$router->get('admin/user-growth', 'AdminStatsController::userGrowth');
$router->get('admin/lesson-engagement', 'AdminStatsController::lessonEngagement');
$router->get('admin/recent-activity', 'AdminStatsController::recentActivity');

$router->get('admin/analytics/overview', 'AnalyticsController::overview');
$router->get('admin/analytics/user-growth', 'AnalyticsController::userGrowth');
$router->get('admin/analytics/submission-activity', 'AnalyticsController::submissionActivity');
$router->get('admin/analytics/learning-paths', 'AnalyticsController::learningPaths');
$router->get('admin/analytics/challenge-difficulty', 'AnalyticsController::challengeDifficulty');
$router->get('admin/analytics/lesson-performance', 'AnalyticsController::lessonPerformance');
$router->get('admin/analytics/session-stats', 'AnalyticsController::sessionStats');
$router->get('admin/analytics/top-performers', 'AnalyticsController::topPerformers');
$router->get('admin/analytics/recent-activity', 'AnalyticsController::recentActivity');
$router->get('admin/analytics/user-stats', 'AnalyticsController::userStats');

// Admin Content Management - Learn Module
$router->get('admin/learn/languages', 'LearnController::getLanguages');
$router->post('admin/learn/languages', 'LearnController::createLanguage');
$router->put('admin/learn/languages/{id}', 'LearnController::updateLanguage');
$router->delete('admin/learn/languages/{id}', 'LearnController::deleteLanguage');

$router->get('admin/learn/lessons', 'LearnController::getLessons');
$router->get('admin/learn/lessons/{id}', 'LearnController::getLesson');
$router->post('admin/learn/lessons', 'LearnController::createLesson');
$router->put('admin/learn/lessons/{id}', 'LearnController::updateLesson');
$router->delete('admin/learn/lessons/{id}', 'LearnController::deleteLesson');
$router->post('lessons', 'ApiLessonsController::save'); // Duplicate? Consider removing

$router->get('admin/learn/sections', 'LearnController::getSections');
$router->post('admin/learn/sections', 'LearnController::createSection');
$router->put('admin/learn/sections/{id}', 'LearnController::updateSection');
$router->delete('admin/learn/sections/{id}', 'LearnController::deleteSection');
$router->post('admin/learn/lessons/{id}/reorder-sections', 'LearnController::reorderSections');

$router->get('admin/learn/paths', 'LearnController::getLearningPaths');
$router->post('admin/learn/paths', 'LearnController::createLearningPath');
$router->put('admin/learn/paths/{id}', 'LearnController::updateLearningPath');
$router->delete('admin/learn/paths/{id}', 'LearnController::deleteLearningPath');

$router->get('admin/learn/analytics/overview', 'LearnController::getAnalyticsOverview');
$router->get('admin/learn/export/{type}', 'LearnController::exportContent');

// Admin Content Management - Challenges Module
$router->get('admin/challenges', 'AdminChallengeController::getChallenges');
$router->get('admin/challenges/{id}', 'AdminChallengeController::getChallenge');
$router->post('admin/challenges', 'AdminChallengeController::createChallenge');
$router->put('admin/challenges/{id}', 'AdminChallengeController::updateChallenge');
$router->delete('admin/challenges/{id}', 'AdminChallengeController::deleteChallenge');

$router->get('admin/challenges/{id}/test-cases', 'AdminChallengeController::getChallengeTestCases');
$router->post('admin/challenges/{id}/test-cases', 'AdminChallengeController::createTestCase');

$router->get('admin/submissions', 'AdminChallengeController::getSubmissions');
$router->get('admin/analytics/challenges', 'AdminChallengeController::getChallengeAnalytics');

$router->get('admin/categories', 'AdminChallengeController::getCategories');
$router->post('admin/categories', 'CategoriesController::create');
$router->put('admin/categories/{id}', 'CategoriesController::update');
$router->delete('admin/categories/{id}', 'CategoriesController::delete');


// =============================================================================
// DEVELOPMENT ROUTES - For development purposes only
// =============================================================================

$router->get('create-migration/{migration_class}', 'MigrationController::create_migration');
$router->get('migrate', 'MigrationController::migrate');
$router->get('rollback', 'MigrationController::rollback');

$router->post('execute', 'ExecController::execute');