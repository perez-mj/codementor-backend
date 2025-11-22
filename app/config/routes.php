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

//API endpoints
$router->post('login', 'ApiController::login');
$router->post('logout', 'ApiController::logout');
$router->post('register', 'ApiController::register');
$router->put('update/{id}', 'ApiController::update');
$router->delete('delete/{id}', 'ApiController::delete');
$router->get('list', 'ApiController::list');
$router->get('profile', 'ApiController::profile');
$router->post('refresh', 'ApiController::refresh');
$router->get('verify-email', 'ApiController::verify_email');

// Learn
$router->get('languages', 'ApiLanguageController::list');
$router->get('languages/{slug}', 'ApiLanguageController::get');
$router->get('languages/{lang_id}/lessons', 'ApiLessonsController::listByLanguage');
$router->get('languages/{lang_id}/lessons/{topic}', 'ApiLessonsController::getLesson');
$router->post('lessons', 'ApiLessonsController::save'); // admin only

// Admin Stats endpoints
$router->get('admin/stats', 'AdminStatsController::stats');
$router->get('admin/user-growth', 'AdminStatsController::userGrowth');
$router->get('admin/lesson-engagement', 'AdminStatsController::lessonEngagement');
$router->get('admin/recent-activity', 'AdminStatsController::recentActivity');

//Migration
$router->get('create-migration/{migration_class}', 'MigrationController::create_migration');
$router->get('migrate', 'MigrationController::migrate');
$router->get('rollback', 'MigrationController::rollback');

// User Stats endpoints
$router->get('user_stats', 'UserStatsController::get_stats');
$router->put('user_stats', 'UserStatsController::update_stats');

// Categories endpoints
$router->get('categories', 'CategoriesController::list');
$router->get('categories/{id}', 'CategoriesController::get');
$router->post('categories', 'CategoriesController::create');
$router->put('categories/{id}', 'CategoriesController::update');
$router->delete('categories/{id}', 'CategoriesController::delete');

// Challenges endpoints
$router->get('challenges', 'ChallengesController::list');
$router->get('challenges/{id|slug}', 'ChallengesController::get');
$router->post('challenges', 'ChallengesController::create');
$router->put('challenges/{id}', 'ChallengesController::update');
$router->delete('challenges/{id}', 'ChallengesController::delete');

// Submissions endpoints
$router->get('submissions', 'SubmissionsController::list');
$router->get('submissions/{id}', 'SubmissionsController::get');

// Note: In a real system, submission creation would likely be handled by a separate service
$router->post('submissions', 'SubmissionsController::create');

// Achievements endpoints
$router->get('achievements', 'AchievementsController::list');
$router->get('achievements/{id}', 'AchievementsController::get');

// Settings endpoints
$router->get('settings', 'UserSettingsController::get_all');
$router->put('settings', 'UserSettingsController::update');


// Analytics endpoint
$router->get('analytics/overview', 'AnalyticsController::overview');
$router->get('analytics/user-growth', 'AnalyticsController::userGrowth');
$router->get('analytics/submission-activity', 'AnalyticsController::submissionActivity');
$router->get('analytics/learning-paths', 'AnalyticsController::learningPaths');
$router->get('analytics/challenge-difficulty', 'AnalyticsController::challengeDifficulty');
$router->get('analytics/lesson-performance', 'AnalyticsController::lessonPerformance');
$router->get('analytics/session-stats', 'AnalyticsController::sessionStats');
$router->get('analytics/top-performers', 'AnalyticsController::topPerformers');
$router->get('analytics/recent-activity', 'AnalyticsController::recentActivity');
$router->get('analytics/user-stats', 'AnalyticsController::userStats');