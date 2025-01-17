<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function create()
    {
        $tday1 = date("Y-m-d");
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $cek = DB::table('presensi')->where('tgl_presensi', $tday1)->where('kdpeg', $kdpeg)->count();
        return view('presensi.create', compact('cek'));
    }
    //method store
    public function store(Request $request)
    {
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $latitudekantor = 0.07618631880945345;
        $longitudekantor = 111.50072513704939;
        $lokasi = $request->lokasi;
        $lokasiuser = explode(",", $lokasi);
        //$latitudeuser = $lokasiuser[0];
        //$longitudeuser = $lokasiuser[1];
        $latitudeuser = $latitudekantor;
        $longitudeuser = $longitudekantor;

        $jarak = $this->distance($latitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]);

        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('kdpeg',  $kdpeg)->count();
        if ($cek > 0) {
            $ket = "out";
        } else {
            $ket = "in";
        }
        $image = $request->image;
        $folderPath = "public/uploads/absensi/";
        $formatName =  $kdpeg . "-" . $tgl_presensi . "-" . $ket;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;
        if ($radius > 2000) {
            echo "error|Maaf Anda di luar Radius, Jarak Anda " . $radius . " meter dari Kantor|radius";
        } else {
            if ($cek > 0) {
                $data_pulang = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName,
                    'lokasi_out' => $lokasi
                ];
                $update = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('kdpeg',  $kdpeg)->update($data_pulang);
                if ($update) {
                    echo "success|Absen Pulang Sukses|out";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf, Gagal Absen|out";
                }
            } else {
                $data = [
                    'kdpeg' => $kdpeg,
                    'tgl_presensi' => $tgl_presensi,
                    'jam_in' => $jam,
                    'foto_in' => $fileName,
                    'lokasi_in' => $lokasi
                ];
                $simpan = DB::table('presensi')->insert($data);

                if ($simpan) {
                    echo "success|Absen Masuk Sukses|in";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Maaf Gagal absen, Hubungi Tim IT|in";
                }
            }
        }
    }
    //Dinstance
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    public function editprofile()
    {
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $karyawan = DB::table('karyawan')->where('kdpeg', $kdpeg)->first();

        return view('presensi.editprofile', compact('karyawan'));
    }

    public function updateprofile(Request $request)
    {
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $nama_lengkap = $request->nama_lengkap;
        $no_hp = $request->no_hp;
        $password = Hash::make($request->password);
        $karyawan = DB::table('karyawan')->where('kdpeg', $kdpeg)->first();
        if ($request->hasFile('foto')) {
            $foto = $kdpeg . "." . $request->file('foto')->getClientOriginalExtension();
        } else {
            $foto = $karyawan->foto;
        }
        if (empty($request->password)) {
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                'foto' => $foto
            ];
        } else {
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                'password' => $password,
                'foto' => $foto
            ];
        }
        $update = DB::table('karyawan')->where('kdpeg', $kdpeg)->update($data);
        if ($update) {
            if ($request->hasFile('foto')) {
                $folderPath = "public/uploads/karyawan/";
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with(['success' => 'Data Berhasil Di Update']);
        } else {
            return Redirect::back()->with(['error' => 'Data gagal Di Update']);
        }
    }
    public function histori()
    {
        $namabulan = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        return view('presensi.histori', compact('namabulan'));
    }

    public function gethistori(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;

        $histori = DB::table('presensi')
            ->whereRaw('MONTH(tgl_presensi)="' . $bulan . '"')
            ->whereRaw('YEAR(tgl_presensi)="' . $tahun . '"')
            ->where('kdpeg', $kdpeg)
            ->orderBy('tgl_presensi')
            ->get();
        return view('presensi.gethistori', compact('histori'));
    }

    public function izin()
    {
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $dataizin = DB::table('pengajuan_izin')->where('kdpeg', $kdpeg)->get();
        return view('presensi.izin', compact('dataizin'));
    }

    public function buatizin()
    {
        return view('presensi.buatizin');
    }

    public function storeizin(Request $request)
    {
        $kdpeg = Auth::guard('karyawan')->user()->kdpeg;
        $tgl_izin = $request->tgl_izin;
        $status = $request->status;
        $keterangan = $request->keterangan;

        $data = [
            'kdpeg' => $kdpeg,
            'tgl_izin' => $tgl_izin,
            'status' => $status,
            'keterangan' => $keterangan
        ];

        $simpan = DB::table('pengajuan_izin')->insert($data);

        if ($simpan) {
            return redirect('/presensi/izin')->with(['success' => 'Berhasil Disimpan']);
        } else {
            return redirect('/presensi/izin')->with(['error' => 'Gagal Disimpan']);
        }
    }
}
