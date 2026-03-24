<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::updateOrCreate(
            ['email' => 'admin@discovergrp.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone'    => '+1-555-000-0001',
                'city'     => 'New York',
                'country'  => 'USA',
                'role'     => 'admin',
            ]
        );

        // Demo customers
        $customers = [
            ['name' => 'Alice Johnson',  'email' => 'alice@example.com',  'city' => 'London',    'country' => 'UK'],
            ['name' => 'Bob Martinez',   'email' => 'bob@example.com',    'city' => 'Madrid',    'country' => 'Spain'],
            ['name' => 'Carol Williams', 'email' => 'carol@example.com',  'city' => 'Sydney',    'country' => 'Australia'],
            ['name' => 'David Lee',      'email' => 'david@example.com',  'city' => 'Toronto',   'country' => 'Canada'],
            ['name' => 'Emma Garcia',    'email' => 'emma@example.com',   'city' => 'Paris',     'country' => 'France'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                array_merge($customer, [
                    'password' => Hash::make('password'),
                    'phone'    => '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'role'     => 'user',
                ])
            );
        }
    }
}
