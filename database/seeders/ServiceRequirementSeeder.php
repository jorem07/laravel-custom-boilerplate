<?php

namespace Database\Seeders;

use App\Models\ServiceRequirement;
use Illuminate\Database\Seeder;

class ServiceRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $requirementsMap = [
            1 => [
                "Service Request Slip with Signatures", "Statement of Account", "Quotation",
                "Certificate of Eligibility with Signature", "Unified Intake Sheet", "Prescription",
                "Medical Certificate", "Hospital Bill", "Medical Lab Request",
                "Barangay Certificate of Indigency", "Pharmacy Slip"
            ],
            2 => [
                "Clinical/Referral Form", "Transportation Slip", "Trip Ticket",
                "Panumpa", "Indigency"
            ],
            3 => [
                "Service Request Slip with Signatures", "Statement of Account",
                "Brgy. Certificate of Indigency", "Death Certificate", "Funeral Contract"
            ],
            4 => [
                "Brgy. Certificate of Idigency", "Certificate"
            ],
            5 => [
                "Service Request Slip with Signatures", "School ID", "Letter Routing Slip",
                "Certificate of Enrollment", "Latest Statement of Account",
                "Brgy. Certificate of Indigency", "Certificate of Eligibility with Signatures"
            ],
            6 => [
                "Brgy. Certificate of Idigency"
            ],
        ];

        // Truncate first
        ServiceRequirement::query()->truncate();

        foreach ($requirementsMap as $serviceId => $reqs) {
            foreach ($reqs as $index => $req) {
                ServiceRequirement::create([
                    'office_service_id' => $serviceId,
                    'description' => $req,
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
