<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Services\UssdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetUssdUser
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isValidUssdRequest = (new UssdService)->isValidUssdRequest();
        $ussdMsisdn = $request->header('USSD-MSISDN');
        $hasUssdMobileNumber = filled($ussdMsisdn);

        if ($isValidUssdRequest && $hasUssdMobileNumber) {

            $ussdMsisdn = '+' . ltrim($ussdMsisdn, '+');

            $user = User::where('mobile_number', $ussdMsisdn)->first();

            if (!$user) {
                $user = User::create([
                    'mobile_number' => $ussdMsisdn
                ]);
            }

            Auth::setUser($user);
        }

        return $next($request);
    }
}
