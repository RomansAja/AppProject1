<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tday = date("Y-m-d");
        $bulanini = date("m") * 1;
        $tahunini = date("Y");
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $presensiToday = DB::table('presensi')->where('kdpeg',  $kdpeg)->where('tgl_presensi', $tday)->first();
        $historibulanini = DB::table('presensi')
            ->where('kdpeg',  $kdpeg)
            ->whereRaw('MONTH(tgl_presensi) ="' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_presensi) ="' . $tahunini . '"')
            ->orderBy('tgl_presensi')
            ->get();

        $rekappresensi = DB::table('presensi')
            ->selectRaw('COUNT(kdpeg) as jmlhadir, SUM(IF(jam_in > "07:30",1,0)) as jmlterlambat')
            ->where('kdpeg',  $kdpeg)
            ->whereRaw('MONTH(tgl_presensi) ="' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_presensi) ="' . $tahunini . '"')
            ->first();

        $leaderboard = DB::table('presensi')
            ->join('karyawan', 'presensi.kdpeg', '=', 'karyawan.kdpeg')
            ->where('tgl_presensi', $tday)
            ->orderBy('jam_in')
            ->get();

        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

        $rekapizin = DB::table('pengajuan_izin')
            ->selectRaw('SUM(IF(status="i",1,0)) as jmlizin, SUM(IF(status="s",1,0)) as jmlsakit')
            ->where('kdpeg', $kdpeg)
            ->whereRaw('MONTH(tgl_izin) ="' . $bulanini . '"')
            ->whereRaw('YEAR(tgl_izin) ="' . $tahunini . '"')
            ->where('status_approved', 1)
            ->first();

        return view('dashboard.dashboard', compact(
            'presensiToday',
            'historibulanini',
            'namabulan',
            'bulanini',
            'tahunini',
            'rekappresensi',
            'leaderboard',
            'rekapizin'
        ));
    }

    public function dashboardadmin(){
        return view('dashboard.dashboardadmin');
    }
}
