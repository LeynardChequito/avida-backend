<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\JobApplication;
use App\Models\Inquiry;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Traffic;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * ✅ Fetch Admin Dashboard KPIs
     */
    public function getDashboardStats()
    {
        try {
            \Log::info('📊 Fetching dashboard stats');
    
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
            \Log::error('❌ Error in getDashboardStats', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
    
    /**
     * ✅ Fetch Graph Data (Monthly Property Listings)
     */
    public function getPropertyTrends()
    {
        try {
            \Log::info('🔍 Starting getPropertyTrends');
    
            $data = DB::table('properties')
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->whereNotNull('created_at')
                ->groupByRaw('MONTH(created_at)')
                ->orderByRaw('MONTH(created_at)')
                ->get();
    
            \Log::info('✅ Property Trends Fetched', ['data' => $data]);
    
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('❌ Error in getPropertyTrends:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'error' => 'Server Error',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * ✅ Fetch Graph Data (Inquiry Trends)
     */
public function getInquiryTrends()
{
    $trends = Inquiry::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get()
        ->map(function ($item) {
            return [
                'month' => sprintf('%04d-%02d', $item->year, $item->month), // Format: YYYY-MM
                'count' => $item->count,
            ];
        });

    return response()->json($trends);
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
