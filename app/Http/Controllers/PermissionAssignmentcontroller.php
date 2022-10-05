<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Silber\Bouncer\BouncerFacade;

class PermissionAssignmentcontroller extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, User $user)
    {
        BouncerFacade::scope()->onceTo($request->input('scope'), function () use ($request, $user) {
            $action = $request->boolean('allow') ? 'allow' : 'disallow';

            BouncerFacade::$action($user)->to($request->input('ability'), $request->input('class'));
        });

        return response()->json();
    }
}
