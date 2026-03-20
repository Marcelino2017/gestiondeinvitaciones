<?php

namespace App\Jobs;

use App\Mail\OrganizationInvitationMail;
use App\Models\Invitation;
use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendOrganizationInvitationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public int $organizationId,
        public int $invitationId,
    ) {}

    public function handle(): void
    {
        $organization = Organization::query()->findOrFail($this->organizationId);
        $invitation = Invitation::query()->findOrFail($this->invitationId);

        Mail::to($invitation->email)
            ->send(new OrganizationInvitationMail($organization, $invitation));
    }
}
