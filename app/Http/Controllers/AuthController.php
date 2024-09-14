<?php
namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminCode;
use App\Models\Department;
use App\Models\Level;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use HttpResponses;
    // Manager registration
    public function registerManager(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'bank_account' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',
            'department' => 'nullable|string|exists:departments,name',  // Validate department name
            'level' => 'nullable|string|exists:levels,name',  // Validate level name
        ]);

        // Find the department and level based on the name
        $department = $request->department ? Department::where('name', $request->department)->first() : null;
        $level = $request->level ? Level::where('name', $request->level)->first() : null;

        // Create the manager
        $manager = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bank_account' => $request->bank_account,
            'password' => Hash::make($request->password),
            'department_id' => $department ? $department->id : null,  // Set department ID
            'level_id' => $level ? $level->id : null,  // Set level ID
            'role' => 'manager',  // Set role to manager
        ]);

        // Generate a unique code for employees to use during registration
        $employeeCode = Str::random(10);

        // Save the code in the `admin_codes` table
        AdminCode::create([
            'code' => $employeeCode,
            'manager_id' => $manager->id,
            'status' => 'active',
        ]);

        // Return the manager details and employee code
        return response()->json([
            'message' => 'Manager registration successful',
            'manager' => $manager,
            'employee_code' => $employeeCode,
        ], 201);
    }

    // Employee registration using the manager's employee code
    public function registerEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'bank_account' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',
            'manager_code' => 'required|string|exists:admin_codes,code,status,active',
            'department' => 'required|string|exists:departments,name',  // Validate department name
            'level' => 'required|string|exists:levels,name',  // Validate level name
        ]);

        // Find the corresponding manager using the employee code
        $adminCode = AdminCode::where('code', $request->manager_code)
            ->where('status', 'active')
            ->firstOrFail();

        // Find the department and level based on the name
        $department = $request->department ? Department::where('name', $request->department)->first() : null;
        $level = $request->level ? Level::where('name', $request->level)->first() : null;

        // Create the employee and associate them with the manager
        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'bank_account' => $request->bank_account,
            'password' => Hash::make($request->password),
            'manager_id' => $adminCode->manager_id,
            'department_id' => $department ? $department->id : null,  // Set department ID
            'level_id' => $level ? $level->id : null,  // Set level ID
            'role' => 'employee',  // Set role to employee
        ]);

        // Optionally update code status to "used" if necessary
        // $adminCode->update(['status' => 'used']);

        // Return the employee details
        return response()->json([
            'message' => 'Employee registration successful',
            'employee' => $employee,
        ], 201);
    }


    // Login functionality
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Generate a token for the user
        $token = $user->createToken('Api token of ' . $user->name)->plainTextToken;

        // If the user is a manager, include their employee code in the response
        if ($user->role === 'manager') {
            $adminCode = AdminCode::where('manager_id', $user->id)
                ->where('status', 'active')
                ->first();

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'employee_code' => $adminCode ? $adminCode->code : null,
                'token' => $token,
            ]);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Logout functionality
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            'message' => Auth::user()->name . ', you have successfully logged out and your token has been deleted',
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'USER NOT FOUND',
            ]);        }
    
        // Generate a random 6-digit code
        $code = mt_rand(100000, 999999);
    
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => now()
            ]
        );
    
        // Send the code via gmail
        Mail::raw("Your password reset code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Password Reset Code');
        });
    
        return response()->json([
            'message' =>  'Password reset code sent to your email',
        ]);    }
    
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|numeric',
        'password' => 'required|min:8|confirmed',
    ]);

    // Retrieve the password reset entry
    $resetEntry = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->code)
        ->first();

    if (!$resetEntry) {
        return response()->json([
            'message' => 'Invalid code',
        ]);    }

    // Reset the user's password
    $user = User::where('email', $request->email)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    // Delete the password reset entry
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
    return response()->json([
        'message' => 'Password has been reset successfully',
    ]);

}  
    public function getProfile(Request $request)
    {
        // Get the authenticated user
        $employee = Auth::user();

        // Check if the authenticated user is an employee
        if ($employee->role !== 'employee') {
            return response()->json([
                'message' => 'Only employees can access this data.',
            ], 403);
        }

        // Return the employee's profile details
        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'profile' => $employee,
        ], 200);
    }
}
