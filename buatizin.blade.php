@extends('layouts.absen');
@section('header')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/css/materialize.min.css">
    <style>
        .datepicker-modal {
            max-height: 430px !important;
        }

        .datepicker-date-display {
            background-color: #2c20e7 !important;
        }

        .datepicker-cancel {
            color: #0b0b14 !important;
        }

        .datepicker-done {
            color: #0d0d16 !important;
        }
    </style>
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">Form Pengajuan</div>
        <div class="right"></div>
    </div>
@endsection

@section('content')
    <div class="row" style="margin-top:70px">
        <div class="col">
            <form method = "POST" action="/presensi/storeizin" id="frmizin">
                @csrf
                <div class="form-group">
                    <input type="text" id="tgl_izin" name="tgl_izin" class="form-control datepicker"
                        placeholder="Tanggal">
                </div>
                <div class="form-group">
                    <select name="status" id="status" class="form-control">
                        <option value="">Status</option>
                        <option value="i">Izin</option>
                        <option value="s">Sakit</option>
                        <option value="p">Perbaikan</option>
                        <option value="t">Tugas Luar</option>
                    </select>
                </div>
                <div class="form-group">
                    <textarea name="keterangan" id="keterangan" cols="30" rows="5" class="form-control" placeholder="Keterangan"></textarea>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary w-100">Kirim</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('myscript')
    <script>
        var currYear = (new Date()).getFullYear();

        $(document).ready(function() {
            $(".datepicker").datepicker({
                //defaultDate: new Date(currYear - 0, 1, 31),
                // setDefaultDate: new Date(2000,01,31),
                //maxDate: new Date(currYear - 0, 12, 31),
                //yearRange: [2000, currYear - 0],
                format: "yyyy-mm-dd"
            });
            $("#frmizin").submit(function() {
                var tgl_izin = $("#tgl_izin").val();
                var status = $("#status").val();
                var keterangan = $("#keterangan").val();
                if (tgl_izin == "") {
                    Swal.fire({
                        title: 'Maaf!',
                        text: 'Tanggal Harus Diisi !',
                        icon: 'warning'
                    });
                    return false;
                } else if (status == "") {
                    Swal.fire({
                        title: 'Maaf!',
                        text: 'Status Harus Diisi !',
                        icon: 'warning'
                    });
                    return false;
                } else if (keterangan == "") {
                    Swal.fire({
                        title: 'Maaf!',
                        text: 'Keterangan Harus Diisi !',
                        icon: 'warning'
                    });
                    return false;
                }

            });
        });
    </script>
@endpush
