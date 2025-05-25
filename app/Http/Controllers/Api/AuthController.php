<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\Account;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register new user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Check if email already exists
            if (Account::where('email', $request->email)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email already in use',
                    'errors' => [
                        'email' => ['This email is already registered']
                    ]
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:accounts',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors()
                ], 400);
            }

            $result = $this->authService->register($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input data',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors()
                ], 400);
            }

            $result = $this->authService->login($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $this->authService->logout();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Refresh authentication token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            $result = $this->authService->refresh();
            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            $user = $this->authService->getCurrentUser();
            return response()->json([
                'status' => 'success',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'current_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed|different:current_password',
            ]);

            $this->authService->changePassword($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request password reset
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestPasswordReset(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $this->authService->requestPasswordReset($validatedData['email']);

            return response()->json([
                'status' => 'success',
                'message' => 'If your email is registered, you will receive password reset instructions.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify email (placeholder for future implementation)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'token' => 'required|string',
            ]);

            // TODO: Implement email verification
            // 1. Validate token
            // 2. Mark email as verified
            // 3. Return success response

            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email verification failed'
            ], 400);
        }
    }

    /**
     * Resend email verification
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendEmailVerification()
    {
        try {
            // TODO: Implement resend email verification
            // 1. Check if user email is already verified
            // 2. Generate new verification token
            // 3. Send verification email

            return response()->json([
                'status' => 'success',
                'message' => 'Verification email sent successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification email'
            ], 500);
        }
    }
}

// <?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Services\AuthService;
// use Illuminate\Http\Request;
// use Illuminate\Validation\ValidationException;

// class AuthController extends Controller
// {
//     protected $authService;

//     public function __construct(AuthService $authService)
//     {
//         $this->authService = $authService;
//         $this->middleware('auth:api', ['except' => ['register', 'login', 'requestPasswordReset']]);
//     }

//     /**
//      * Register new user
//      *
//      * @param Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function register(Request $request)
//     {
//         try {
//             // Validate dữ liệu đầu vào
//             $validatedData = $request->validate([
//                 'email' => 'required|string|email|max:255|unique:accounts,email',
//                 'password' => 'required|string|min:8|confirmed',
//                 'name' => 'required|string|max:255',
//                 'date_of_birth' => 'nullable|date|before:today',
//                 'gender' => 'nullable|in:male,female,other',
//                 'phone' => 'nullable|string|max:20|unique:user_profiles,phone',
//                 'address' => 'nullable|string|max:500',
//                 'city' => 'nullable|string|max:255',
//                 'district' => 'nullable|string|max:255',
//                 'ward' => 'nullable|string|max:255',
//                 'avatar' => 'nullable|url|max:255',
//             ]);

//             $data = $this->authService->register($validatedData);

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'User registered successfully',
//                 'data' => $data
//             ], 201);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation failed',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Login user
//      *
//      * @param Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function login(Request $request)
//     {
//         try {
//             $validatedData = $request->validate([
//                 'email' => 'required|email|max:255',
//                 'password' => 'required|string|min:8',
//             ]);

//             $data = $this->authService->login($validatedData);

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Login successful',
//                 'data' => $data
//             ], 200);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Login failed',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Logout user
//      *
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function logout()
//     {
//         try {
//             $this->authService->logout();

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Successfully logged out'
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Refresh authentication token
//      *
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function refresh()
//     {
//         try {
//             $data = $this->authService->refresh();

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Token refreshed successfully',
//                 'data' => $data
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 401);
//         }
//     }

//     /**
//      * Get current authenticated user
//      *
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function me()
//     {
//         try {
//             $data = $this->authService->getCurrentUser();

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'User information retrieved successfully',
//                 'data' => $data
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Change user password
//      *
//      * @param Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function changePassword(Request $request)
//     {
//         try {
//             $validatedData = $request->validate([
//                 'current_password' => 'required|string|min:8',
//                 'new_password' => 'required|string|min:8|confirmed|different:current_password',
//             ]);

//             $this->authService->changePassword($validatedData);

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Password changed successfully'
//             ], 200);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation failed',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Request password reset
//      *
//      * @param Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function requestPasswordReset(Request $request)
//     {
//         try {
//             $validatedData = $request->validate([
//                 'email' => 'required|email|max:255',
//             ]);

//             $this->authService->requestPasswordReset($validatedData['email']);

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'If your email is registered, you will receive password reset instructions.'
//             ], 200);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation failed',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => $e->getMessage()
//             ], 500);
//         }
//     }

//     /**
//      * Verify email (placeholder for future implementation)
//      *
//      * @param Request $request
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function verifyEmail(Request $request)
//     {
//         try {
//             $validatedData = $request->validate([
//                 'token' => 'required|string',
//             ]);

//             // TODO: Implement email verification
//             // 1. Validate token
//             // 2. Mark email as verified
//             // 3. Return success response

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Email verified successfully'
//             ], 200);

//         } catch (ValidationException $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Validation failed',
//                 'errors' => $e->errors()
//             ], 422);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Email verification failed'
//             ], 400);
//         }
//     }

//     /**
//      * Resend email verification
//      *
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function resendEmailVerification()
//     {
//         try {
//             // TODO: Implement resend email verification
//             // 1. Check if user email is already verified
//             // 2. Generate new verification token
//             // 3. Send verification email

//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Verification email sent successfully'
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Failed to send verification email'
//             ], 500);
//         }
//     }
// }
