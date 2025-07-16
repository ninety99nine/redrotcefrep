<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;

class StorePermission
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new middleware instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user is a super admin
        $isSuperAdmin = $this->authService->isSuperAdmin($user);

        // Get store_id from route or input
        $storeId = $request->route('store') ?? $request->input('store_id');

        if ($storeId) {

            // Validate store_id exists
            $storeExists = Store::where('id', $storeId)->exists();

            if (!$storeExists) {
                return response()->json(['message' => 'This store does not exist'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Super admins can access any store
            if (!$isSuperAdmin) {

                // Check if the user is a member of the store
                $isStoreMember = $user->stores()->where('stores.id', $storeId)->exists();

                if (!$isStoreMember) {
                    return response()->json(['message' => 'You do not have access to this store'], Response::HTTP_FORBIDDEN);
                }

            }

            // Set the permissions team ID for Spatie's permission checks
            setPermissionsTeamId($storeId);

        } elseif (!$isSuperAdmin) {

            // Non-super admins must provide an store_id
            return response()->json(['message' => 'The store ID is required'], Response::HTTP_UNPROCESSABLE_ENTITY);

        }

        return $next($request);

    }
}
