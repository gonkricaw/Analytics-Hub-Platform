<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General Application Settings
        SystemConfiguration::setConfig('app_name', 'Indonet Analytics Hub', 'string', 'general');
        SystemConfiguration::setConfig('app_description', 'A platform for analytics and insights', 'string', 'general');
        SystemConfiguration::setConfig('company_name', 'Indonet', 'string', 'general');
        SystemConfiguration::setConfig('contact_email', 'powerbi@indonet.id', 'string', 'general');
        SystemConfiguration::setConfig('copyright_text', '© ' . date('Y') . ' Indonet. All rights reserved.', 'string', 'general');

        // Home Page Settings
        SystemConfiguration::setConfig('marquee_text', 'Welcome to Indonet Analytics Hub - Your one-stop platform for analytics and insights', 'string', 'home');
        SystemConfiguration::setConfig('marquee_enabled', '1', 'boolean', 'home');
        SystemConfiguration::setConfig('marquee_speed', '5', 'integer', 'home');

        // Jumbotron Settings
        SystemConfiguration::setConfig('jumbotron_enabled', '1', 'boolean', 'home');
        SystemConfiguration::setConfig('jumbotron_items', json_encode([
            [
                'image' => '/images/jumbotron/slide1.jpg',
                'title' => 'Welcome to the Analytics Hub',
                'subtitle' => 'Centralized data analytics and insights platform',
                'button_text' => 'Learn More',
                'button_link' => '/about'
            ],
            [
                'image' => '/images/jumbotron/slide2.jpg',
                'title' => 'Real-Time Analytics',
                'subtitle' => 'Monitor your data in real-time with interactive dashboards',
                'button_text' => 'View Dashboards',
                'button_link' => '/dashboards'
            ],
            [
                'image' => '/images/jumbotron/slide3.jpg',
                'title' => 'Custom Reports',
                'subtitle' => 'Generate custom reports tailored to your needs',
                'button_text' => 'Create Report',
                'button_link' => '/reports'
            ]
        ]), 'json', 'home');

        // Appearance Settings
        SystemConfiguration::setConfig('logo_path', '/images/logo.png', 'string', 'appearance');
        SystemConfiguration::setConfig('login_background', '/images/login-bg.jpg', 'string', 'appearance');
        SystemConfiguration::setConfig('primary_color', '#8C3EFF', 'string', 'appearance');
        SystemConfiguration::setConfig('dark_mode', '1', 'boolean', 'appearance');
        SystemConfiguration::setConfig('default_profile_picture', '/images/default-avatar.png', 'string', 'appearance');

        // Dashboard Widget Settings
        SystemConfiguration::setConfig('dashboard_refresh_interval', '300', 'integer', 'dashboard'); // 5 minutes
        SystemConfiguration::setConfig('widget_online_users_enabled', '1', 'boolean', 'dashboard');
        SystemConfiguration::setConfig('widget_login_chart_enabled', '1', 'boolean', 'dashboard');
        SystemConfiguration::setConfig('widget_notifications_enabled', '1', 'boolean', 'dashboard');
        SystemConfiguration::setConfig('widget_popular_menus_enabled', '1', 'boolean', 'dashboard');

        // Security Settings
        SystemConfiguration::setConfig('inactivity_timeout', '900', 'integer', 'security'); // 15 minutes
        SystemConfiguration::setConfig('password_expiry_days', '90', 'integer', 'security');
        SystemConfiguration::setConfig('max_login_attempts', '5', 'integer', 'security');
    }
}
