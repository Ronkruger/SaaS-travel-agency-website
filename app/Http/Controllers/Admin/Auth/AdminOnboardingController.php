<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnboardingController extends Controller
{
    const DEPARTMENTS = [
        'executives' => [
            'label'     => 'Executives Department',
            'positions' => [
                'Operations Manager',
                'Division Manager',
                'Executive Secretary',
                'Executive Assistant',
            ],
        ],
        'visa' => [
            'label'     => 'Visa Department',
            'positions' => [
                'Visa Department Head',
                'Team Lead - Visa Officer',
                'Visa Officer',
                'General Admin — Visa',
                'VFS and Airport Assistance Officer',
                'Visa Assistant Facilitator',
            ],
        ],
        'booking' => [
            'label'     => 'Booking Department',
            'positions' => [
                'Booking Supervisor',
                'Booking Officer',
                'General Admin for Booking',
            ],
        ],
        'marketing' => [
            'label'     => 'Marketing Department',
            'positions' => [
                'Marketing Officer',
                'Graphic Artist',
            ],
        ],
        'sales' => [
            'label'     => 'Sales Department',
            'positions' => [
                'Travel Sales Agent',
                'General Admin — Sales',
            ],
        ],
        'customer_service' => [
            'label'     => 'Customer Service Department',
            'positions' => [
                'Customer Service Refund',
                'Account Relations Manager (ARM)',
                'Team Lead - ARM',
                'General Admin - ARM',
            ],
        ],
        'hr' => [
            'label'     => 'Human Resource Department',
            'positions' => [
                'HR Assistant — Recruitment',
                'HR Officer',
                'General Admin - HR',
            ],
        ],
        'it' => [
            'label'     => 'Information & Technology Department',
            'positions' => [
                'IT Manager',
                'IT Systems Administrator',
                'IT Support',
                'Web Developer',
            ],
        ],
        'finance' => [
            'label'     => 'Finance Department',
            'positions' => [
                'Finance Officer',
            ],
        ],
        'rd' => [
            'label'     => 'Research and Development Department',
            'positions' => [
                'Research Development Officer',
            ],
        ],
    ];

    public function show()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->is_onboarded) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.onboarding', [
            'departments' => self::DEPARTMENTS,
        ]);
    }

    public function save(Request $request)
    {
        $departments    = self::DEPARTMENTS;
        $departmentKeys = array_keys($departments);

        $validated = $request->validate([
            'department' => ['nullable', 'string', 'in:' . implode(',', $departmentKeys)],
            'position'   => ['nullable', 'string', 'max:150'],
        ]);

        // If department is provided, ensure position is also provided and valid
        if (!empty($validated['department'])) {
            if (empty($validated['position'])) {
                return back()->withErrors([
                    'position' => 'Please select a position for the chosen department.',
                ])->withInput();
            }

            $validPositions = $departments[$validated['department']]['positions'];
            if (!in_array($validated['position'], $validPositions, true)) {
                return back()->withErrors([
                    'position' => 'The selected position is not valid for the chosen department.',
                ])->withInput();
            }
        }

        $admin = Auth::guard('admin')->user();
        
        $updateData = ['is_onboarded' => true];
        
        // Only update department/position if provided
        if (!empty($validated['department'])) {
            $updateData['department'] = $validated['department'];
        }
        if (!empty($validated['position'])) {
            $updateData['position'] = $validated['position'];
        }
        
        $admin->update($updateData);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome to your agency dashboard, ' . $admin->name . '!');
    }
}
