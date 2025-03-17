<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\JobApplication;
use App\Models\Inquiry;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Traffic;
class AdminDashboardController extends Controller
{
    /**
     * ✅ Fetch Admin Dashboard KPIs
     */
    public function getDashboardStats()
    {
        try {
            $totalProperties = Property::count();
            $totalInquiries = Inquiry::count();
            $totalApplications = JobApplication::count();
            $totalAppointments = Appointment::count();

            return response()->json([
                'total_properties' => $totalProperties,
                'total_inquiries' => $totalInquiries,
                'total_applications' => $totalApplications,
                'total_appointments' => $totalAppointments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ✅ Fetch Graph Data (Monthly Property Listings)
     */
    public function getPropertyTrends()
    {
        $propertyTrends = Property::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($propertyTrends);
    }

    /**
     * ✅ Fetch Graph Data (Inquiry Trends)
     */
    public function getInquiryTrends()
    {
        $inquiryTrends = Inquiry::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($inquiryTrends);
    }

    /**
     * ✅ Fetch Graph Data (Job Applications Trends)
     */
    public function getJobApplicationTrends()
    {
        $applicationTrends = JobApplication::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($applicationTrends);
    }

    /**
     * ✅ Fetch Website Traffic (Simulated Data)
     */

     public function getWebsiteTraffic()
{
    try {
        // ✅ Fetch traffic data from the database
        $traffic = Traffic::select('source', 'visits')
            ->orderBy('visits', 'desc')
            ->get();

        return response()->json(['sources' => $traffic], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
    }
}
}
