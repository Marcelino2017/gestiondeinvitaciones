<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invitation\AcceptInvitationRequest;
use App\Http\Requests\Invitation\StoreInvitationRequest;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitationService
    ) {}

    public function store(StoreInvitationRequest $request, int $organization): JsonResponse
    {
        $invitation = $this->invitationService->createForOrganization(
            $request->validated(),
            $organization,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Invitacion creada correctamente.',
            'data' => $invitation,
        ], 201);
    }

    public function showByToken(string $token): JsonResponse
    {
        $invitation = $this->invitationService->showByToken($token);

        return response()->json([
            'success' => true,
            'message' => 'Invitacion obtenida correctamente.',
            'data' => $invitation,
        ], 200);
    }

    public function acceptByToken(AcceptInvitationRequest $request, string $token): JsonResponse
    {
        $data = $request->validated();

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $invitation = $this->invitationService->acceptByToken($token, $data);

        return response()->json([
            'success' => true,
            'message' => 'Invitacion aceptada correctamente.',
            'data' => $invitation,
        ], 200);
    }
}
