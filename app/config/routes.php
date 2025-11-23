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

// Auth endpoints
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


// Admin Stats endpoints
$router->get('admin/stats', 'AdminStatsController::stats');
$router->get('admin/user-growth', 'AdminStatsController::userGrowth');
$router->get('admin/lesson-engagement', 'AdminStatsController::lessonEngagement');
$router->get('admin/recent-activity', 'AdminStatsController::recentActivity');

// Analytics endpoint
$router->get('admin/analytics/overview', 'analyticsController::overview');
$router->get('admin/analytics/user-growth', 'analyticsController::userGrowth');
$router->get('admin/analytics/submission-activity', 'analyticsController::submissionActivity');
$router->get('admin/analytics/learning-paths', 'analyticsController::learningPaths');
$router->get('admin/analytics/challenge-difficulty', 'analyticsController::challengeDifficulty');
$router->get('admin/analytics/lesson-performance', 'analyticsController::lessonPerformance');
$router->get('admin/analytics/session-stats', 'analyticsController::sessionStats');
$router->get('admin/analytics/top-performers', 'analyticsController::topPerformers');
$router->get('admin/analytics/recent-activity', 'analyticsController::recentActivity');
$router->get('admin/analytics/user-stats', 'analyticsController::userStats');

// User Management Routes
$router->get('admin/users', 'UsersController::list');
$router->get('admin/users/stats', 'UsersController::stats');
$router->get('admin/users/{id}', 'UsersController::get');
$router->put('admin/users/{id}/role', 'UsersController::updateRole');
$router->post('admin/users/{id}/moderate', 'UsersController::moderate');

// User Progress Routes
$router->get('admin/users/{id}/progress', 'UserProgressController::getProgress');
$router->get('admin/users/{id}/learning-paths', 'UserProgressController::getLearningPaths');

// Submissions Routes
$router->get('admin/users/{id}/submissions', 'SubmissionsController::getUserSubmissions');

// AI Interactions Routes
$router->get('admin/users/{id}/ai-interactions', 'AIInteractionsController::getUserInteractions');

// Learn Management Routes
$router->get('admin/learn/languages', 'LearnController::getLanguages');
$router->post('admin/learn/languages', 'LearnController::createLanguage');
$router->put('admin/learn/languages/{id}', 'LearnController::updateLanguage');
$router->delete('admin/learn/languages/{id}', 'LearnController::deleteLanguage');

$router->get('admin/learn/lessons', 'LearnController::getLessons');
$router->get('admin/learn/lessons/{id}', 'LearnController::getLesson');
$router->post('admin/learn/lessons', 'LearnController::createLesson');
$router->put('admin/learn/lessons/{id}', 'LearnController::updateLesson');
$router->delete('admin/learn/lessons/{id}', 'LearnController::deleteLesson');

$router->get('admin/learn/sections', 'LearnController::getSections');
$router->post('admin/learn/sections', 'LearnController::createSection');
$router->put('admin/learn/sections/{id}', 'LearnController::updateSection');
$router->delete('admin/learn/sections/{id}', 'LearnController::deleteSection');

// Learning Paths Routes
$router->get('admin/learn/paths', 'LearnController::getLearningPaths');
$router->post('admin/learn/paths', 'LearnController::createLearningPath');
$router->put('admin/learn/paths/{id}', 'LearnController::updateLearningPath');
$router->delete('admin/learn/paths/{id}', 'LearnController::deleteLearningPath');

// Analytics Routes
$router->get('admin/learn/analytics/overview', 'LearnController::getAnalyticsOverview');
$router->post('admin/learn/lessons/{id}/reorder-sections', 'LearnController::reorderSections');
$router->get('admin/learn/export/{type}', 'LearnController::exportContent');