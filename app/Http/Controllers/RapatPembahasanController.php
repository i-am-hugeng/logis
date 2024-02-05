<?php

namespace App\Http\Controllers;

use App\Models\MeetingMaterial;
use App\Models\MeetingSchedule;
use App\Models\TransitionTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HasilRapatExport;

class RapatPembahasanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('rapat-pembahasan');
    }

    public function kontenSuperAdmin(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('meeting_schedules')
                ->join('meeting_materials', 'meeting_schedules.id', '=', 'meeting_materials.id_meeting_schedule')
                ->select(
                    'meeting_schedules.id',
                    'meeting_schedules.pic_rapat',
                    'meeting_schedules.tanggal_rapat',
                    'meeting_schedules.status_pembahasan'
                )
                ->selectRaw('COUNT(DISTINCT(meeting_materials.id_sni_lama)) AS jumlah_sni')
                ->groupBy('meeting_schedules.id')
                ->get();

            return DataTables::of($data)->addIndexColumn()->make(true);
        }

        return view('rapat-pembahasan');
    }

    public function detailPembahasan($id)
    {
        $detail_rapat = MeetingSchedule::where('id', $id)->first();

        $pembahasan = DB::table('meeting_materials')
            ->join('meeting_schedules', 'meeting_materials.id_meeting_schedule', '=', 'meeting_schedules.id')
            ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
            ->join('revision_decrees', 'old_standards.id_sk_revisi', '=', 'revision_decrees.id')
            ->join('identifications', 'revision_decrees.id', '=', 'identifications.id_sk_revisi')
            ->join('standard_implementers', 'identifications.id', '=', 'standard_implementers.id_identifikasi')
            ->leftJoin('transition_times', 'meeting_materials.id', 'transition_times.id_sni_lama')
            ->select(
                'revision_decrees.nmr_sni_baru',
                'revision_decrees.jdl_sni_baru',
                'old_standards.nmr_sni_lama',
                'old_standards.jdl_sni_lama',
                'identifications.komtek',
                'meeting_materials.status_sni_lama',
                'transition_times.batas_transisi',
                'meeting_materials.catatan'
            )
            ->groupBy('meeting_materials.id_sni_lama')
            ->where('meeting_schedules.id', '=', $id)
            ->get();

        return response()->json([
            'detail_rapat'  => $detail_rapat,
            'pembahasan'    => $pembahasan,
        ]);
    }

    public function export(Request $request)
    {
        $tanggal_rapat = MeetingSchedule::where('id', $request->id)->first();

        return Excel::download(new HasilRapatExport($request->id), 'hasil_rapat_' . $tanggal_rapat->tanggal_rapat . '.xlsx');
    }

    public function kontenAdmin()
    {
        $data_pic = DB::table('users')
            ->select('id', 'name')
            ->where('name', '=', Auth::user()->name)
            ->first();

        return response()->json([
            'data_pic' => $data_pic,
        ]);
    }

    public function bahasDetail(Request $request, $id)
    {
        $tanggalRapat = MeetingSchedule::find($id);

        if ($request->ajax()) {
            $pembahasan = DB::table('meeting_materials')
                ->join('meeting_schedules', 'meeting_materials.id_meeting_schedule', '=', 'meeting_schedules.id')
                ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
                ->join('revision_decrees', 'old_standards.id_sk_revisi', '=', 'revision_decrees.id')
                ->join('identifications', 'revision_decrees.id', '=', 'identifications.id_sk_revisi')
                ->join('standard_implementers', 'identifications.id', '=', 'standard_implementers.id_identifikasi')
                ->leftJoin('transition_times', 'meeting_materials.id', 'transition_times.id_sni_lama')
                ->select(
                    'revision_decrees.nmr_sni_baru',
                    'revision_decrees.jdl_sni_baru',
                    'old_standards.nmr_sni_lama',
                    'old_standards.jdl_sni_lama',
                    'identifications.komtek',
                    'meeting_materials.status_sni_lama',
                    'transition_times.batas_transisi',
                    'meeting_materials.catatan',
                    'meeting_materials.id',
                )
                ->groupBy('meeting_materials.id_sni_lama')
                ->where('meeting_schedules.id', '=', $id)
                ->get();

            return response()->json([
                'pembahasan'    => $pembahasan,
            ]);
        }

        return view('rapat-pembahasan-detail', compact('tanggalRapat'));
    }

    public function simpanBahasDetail(Request $request, $id)
    {
        // dd($request->all());
        MeetingMaterial::where('id', $id)
            ->update([
                'status_sni_lama' => $request->status,
                'catatan' => nl2br($request->catatan),
            ]);

        if ($request->status == 0) {
            TransitionTime::where('id_sni_lama', $id)->delete();
        }

        if ($request->status == 1) {
            TransitionTime::updateOrCreate(
                [
                    'id_sni_lama' => $id,
                ],
                [
                    'batas_transisi' => $request->batas_transisi,
                ]
            );
        }

        return response()->json([]);
    }

    public function bahasDetailBahanRapat($id)
    {
        $bahanRapat = DB::table('meeting_materials')
            ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
            ->join('revision_decrees', 'old_standards.id_sk_revisi', '=', 'revision_decrees.id')
            ->join('identifications', 'revision_decrees.id', '=', 'identifications.id_sk_revisi')
            ->join('standard_implementers', 'identifications.id', '=', 'standard_implementers.id_identifikasi')
            ->leftJoin('transition_times', 'meeting_materials.id', 'transition_times.id_sni_lama')
            ->select(
                'revision_decrees.nmr_sni_baru',
                'revision_decrees.jdl_sni_baru',
                'old_standards.nmr_sni_lama',
                'old_standards.jdl_sni_lama',
                'identifications.komtek',
                'meeting_materials.status_sni_lama',
                'transition_times.batas_transisi',
                'meeting_materials.catatan',
                'meeting_materials.id',
            )
            ->selectRaw('COUNT(CASE WHEN (standard_implementers.penerap = "-") THEN NULL ELSE 1 END) AS jumlah_penerap')
            ->groupBy('meeting_materials.id_sni_lama')
            ->where('meeting_materials.id', '=', $id)
            ->first();

        // dd($bahanRapat);

        $batasTransisi = TransitionTime::where('id_sni_lama', $id)->first();

        return response()->json([
            'bahanRapat'    => $bahanRapat,
            'batasTransisi'    => $batasTransisi,
        ]);
    }

    public function dt_pembahasan($id, Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('meeting_schedules')
                ->join('meeting_materials', 'meeting_schedules.id', '=', 'meeting_materials.id_meeting_schedule')
                ->join('users', 'meeting_schedules.pic_rapat', '=', 'users.name')
                ->select(
                    'meeting_schedules.id',
                    'meeting_schedules.pic_rapat',
                    'meeting_schedules.tanggal_rapat',
                    'meeting_schedules.status_pembahasan'
                )
                ->selectRaw('COUNT(DISTINCT(meeting_materials.id_sni_lama)) AS jumlah_sni')
                ->groupBy('meeting_schedules.id')
                ->where('users.id', '=', $id)
                ->get();

            return DataTables::of($data)
                ->addColumn('aksi', function ($data) {
                    $button = '<button type="button" name="bahas" id="' . $data->id . '" class="bahas btn bg-gradient-indigo btn-sm" title="bahas"><i class="fas fa-crosshairs"></i></button>';
                    return $button;
                })
                ->rawColumns(['aksi'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('rapat-pembahasan');
    }

    // public function modalKonfirmasiHapusSNI($id)
    // {
    //     $data = DB::table('meeting_materials')
    //         ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
    //         ->select('meeting_materials.id', 'old_standards.nmr_sni_lama')
    //         ->where('meeting_materials.id', $id)
    //         ->first();

    //     return response()->json([
    //         'data' => $data,
    //     ]);
    // }

    // public function dataPembahasan($id)
    // {
    //     $id_rapat = MeetingSchedule::where('id', $id)->first();

    //     $data_pembahasan = DB::table('meeting_materials')
    //         ->join('meeting_schedules', 'meeting_materials.id_meeting_schedule', '=', 'meeting_schedules.id')
    //         ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
    //         ->join('revision_decrees', 'old_standards.id_sk_revisi', '=', 'revision_decrees.id')
    //         ->join('identifications', 'revision_decrees.id', '=', 'identifications.id_sk_revisi')
    //         ->join('standard_implementers', 'identifications.id', '=', 'standard_implementers.id_identifikasi')
    //         ->select(
    //             'meeting_materials.id',
    //             'revision_decrees.nmr_sni_baru',
    //             'revision_decrees.jdl_sni_baru',
    //             'old_standards.nmr_sni_lama',
    //             'old_standards.jdl_sni_lama',
    //             'identifications.komtek'
    //         )
    //         ->selectRaw('COUNT(CASE WHEN (standard_implementers.penerap = "-") THEN NULL ELSE 1 END) AS jumlah_penerap')
    //         ->groupBy('meeting_materials.id_sni_lama')
    //         ->where('meeting_schedules.id', '=', $id)
    //         ->get();

    //     return response()->json([
    //         'data_pembahasan' => $data_pembahasan,
    //         'id_rapat' => $id_rapat,
    //     ]);
    // }

    public function penerapModalSNILama($id)
    {
        $data_penerap = DB::table('standard_implementers')
            ->join('identifications', 'standard_implementers.id_identifikasi', '=', 'identifications.id')
            ->join('revision_decrees', 'identifications.id_sk_revisi', '=', 'revision_decrees.id')
            ->join('old_standards', 'revision_decrees.id', '=', 'old_standards.id_sk_revisi')
            ->join('meeting_materials', 'old_standards.id', '=', 'meeting_materials.id_sni_lama')
            ->select('standard_implementers.penerap')
            ->where('meeting_materials.id', '=', $id)
            ->get();

        return response()->json([
            'data_penerap' => $data_penerap,
        ]);
    }

    // public function hapusSNILama($id)
    // {
    //     $sni_lama = MeetingMaterial::find($id);
    //     $sni_lama->delete();

    //     return response()->json([]);
    // }

    public function finalisasiPembahasan(Request $request)
    {
        // dd($request->all());

        $bahanRapat = MeetingMaterial::where('id_meeting_schedule', $request->id_rapat)->get();

        foreach ($bahanRapat as $item) {
            if ($item->status_sni_lama === null) {
                return response()->json(['error' => 'Ada bahan rapat yang belum dibahas'], 422);
            };
        }

        //simpan status pembahasan rapat
        MeetingSchedule::where('id', $request->id_rapat)
            ->update([
                'status_pembahasan' => 1,
            ]);

        return response()->json([]);
    }

    // public function modalEdit($id)
    // {
    //     $id_rapat = MeetingSchedule::where('id', $id)->first();

    //     $data = DB::table('meeting_schedules')
    //         ->join('meeting_materials', 'meeting_schedules.id', '=', 'meeting_materials.id_meeting_schedule')
    //         ->join('old_standards', 'meeting_materials.id_sni_lama', '=', 'old_standards.id')
    //         ->join('revision_decrees', 'old_standards.id_sk_revisi', '=', 'revision_decrees.id')
    //         ->join('identifications', 'revision_decrees.id', '=', 'identifications.id_sk_revisi')
    //         ->leftJoin('transition_times', 'meeting_materials.id', '=', 'transition_times.id_sni_lama')
    //         ->select(
    //             'meeting_materials.id',
    //             'revision_decrees.nmr_sni_baru',
    //             'revision_decrees.jdl_sni_baru',
    //             'old_standards.nmr_sni_lama',
    //             'old_standards.jdl_sni_lama',
    //             'identifications.komtek',
    //             'meeting_materials.status_sni_lama',
    //             'transition_times.id as id_batas_transisi',
    //             'transition_times.batas_transisi',
    //             'meeting_materials.catatan'
    //         )
    //         ->where('meeting_schedules.id', $id)
    //         ->get();

    //     return response()->json([
    //         'id_rapat' => $id_rapat,
    //         'data' => $data
    //     ]);
    // }

    // public function edit(Request $request)
    // {
    //     $data = $request->all();

    //     // dd($data);

    //     //edit status SNI lama
    //     $catatan = $request->get('catatan');
    //     foreach ($catatan as $item => $value) {
    //         MeetingMaterial::where('id', $data['id_sni_lama'][$item])
    //             ->update([
    //                 'status_sni_lama'   => $data['status_sni_lama'][$item],
    //                 'catatan'           => nl2br($data['catatan'][$item]),
    //             ]);

    //         //simpan batas waktu masa transisi SNI lama
    //         if ($data['status_sni_lama'][$item] == 1) {
    //             TransitionTime::where('id', $data['id_batas_transisi'][$item])
    //                 ->update([
    //                     'batas_transisi' => $data['batas_transisi'][$item],
    //                 ]);
    //         }
    //     }

    //     return response()->json([]);
    // }
}
