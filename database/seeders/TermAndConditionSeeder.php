<?php

namespace Database\Seeders;

use App\Models\TermAndCondition;
use Illuminate\Database\Seeder;

class TermAndConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TermAndCondition::create([
            'title' => 'Indonet Analytics Hub Platform Terms and Conditions',
            'content' => '
# Terms and Conditions for Indonet Analytics Hub Platform

## 1. Introduction

Welcome to the Indonet Analytics Hub Platform. These Terms and Conditions govern your use of our platform and services.

## 2. User Accounts

Users are responsible for maintaining the confidentiality of their account credentials.

## 3. Data Privacy

We respect your privacy and are committed to protecting your personal data.

## 4. Acceptable Use Policy

You agree not to use the platform for any illegal or unauthorized purpose.

## 5. Intellectual Property

All content, features, and functionality of the platform are owned by Indonet.

## 6. Disclaimer of Warranties

The platform is provided "as is" and "as available" without any warranties.

## 7. Limitation of Liability

Indonet shall not be liable for any indirect, incidental, or consequential damages.

## 8. Changes to Terms

We reserve the right to modify these terms at any time.

## 9. Governing Law

These terms shall be governed by the laws of Indonesia.
            ',
            'is_active' => true,
            'effective_date' => now(),
            'version' => '1.0.0'
        ]);
    }
}
