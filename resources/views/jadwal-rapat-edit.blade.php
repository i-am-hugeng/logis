@if(Auth::user()->level != 0)
    <script type="text/javascript">
        window.location = "{{ url('/dashboard') }}";//here double curly bracket
    </script>
@endif

@extends('layouts.main', ['title' => 'Edit Jadwal Rapat'])

@section('content-header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1>Edit Jadwal Rapat</h1>
            </div>
        </div>
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <!-- Default box -->
    <div class="card">
        <input type="hidden" class="id_jadwal_rapat" id="{{ $tanggalRapat->id }}">
        <div class="card-header">
          <div class="card-title">
            <div class="d-flex flex-row">
              <h3>
                Rapat Tanggal :
              </h3>
                <form id="form-edit-bahan-rapat" class="ml-2 d-flex flex-row">
                    <select id="pic_rapat" class="form-control mr-2" name="pic_rapat">
                        @foreach ($data_pic as $pic)
                            <option value="{{ $pic->name }}" {{ $pic->name === $tanggalRapat->pic_rapat ? 'selected' : '' }}>{{ $pic->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" class="form-control mr-2" id="tanggal_rapat" name="tanggal_rapat" placeholder="Masukkan tanggal rapat..." value="{{ $tanggalRapat->tanggal_rapat }}" />
                    <button type="submit" id="tombol-update-jadwal-rapat" class="btn bg-gradient-olive" title="update jadwal rapat"><i class="fas fa-floppy-disk"></i></button>
                </form>
            </div>
          </div>
          
          <div class="card-tools">
            <button id="tombol-tambah-sni-lama" class="btn bg-gradient-indigo" title="tambah bahan rapat"><i class="fas fa-plus"></i></button>
          </div>
        </div>
        <div class="card-body">
            <table class="table table-sm table-bordered table-hover" id="jadwal-rapat-dt">
                <thead class="bg-pink text-white">
                    <tr>
                        <th class="text-center" style="width: 5%">No.</th>
                        <th class="text-center" style="width: 25%">Nomor SNI</th>
                        <th class="text-center" style="width: 60%">Judul SNI</th>
                        <th class="text-center" style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody id="meeting-materials-table">

                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->

    {{-- Modal SNI Lama, Komtek, Penerap --}}
    <div class="modal fade" data-backdrop="static" id="modal-sni-lama">
        <form id="form-sni-lama">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Data SNI Lama, Komtek, dan Penerap</h4>
                        <button type="button" class="close" id="tutup-modal-sni-lama" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table-xl table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:15%">Nomor SNI Lama</th>
                                    <th class="text-center" style="width:45%">Judul SNI Lama</th>
                                    <th class="text-center" style="width:20%">Komtek</th>
                                    <th class="text-center" style="width:10%">Jumlah Penerap</th>
                                    <th class="text-center" style="width:10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-modal-sni-lama">

                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </form>
    </div>
    {{-- End of - Modal SNI Lama, Komtek, Penerap --}}

    {{-- Modal Konfirmasi Hapus Bahan Rapat --}}
    <div class="modal fade" id="modal-konfirmasi-hapus-bahan-rapat">
        <form id="form-konfirmasi-hapus-bahan-rapat">
            <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Hapus Bahan Rapat Masa Transisi SNI</h4>
                    <button type="button" class="close tutup-modal-konfirmasi-hapus-bahan-rapat" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body" id="body-konfirmasi-hapus-bahan-rapat">
                      
                      
                  </div>
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default tutup-modal-konfirmasi-hapus-bahan-rapat" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger tombol-hapus-bahan-rapat">Hapus</button>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
        </form>
        <!-- /.modal-dialog -->
    </div>
    {{-- End of - Modal Konfirmasi Hapus Jadwal Rapat --}}
@endsection
    
@push('css')
    
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast'
                },
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            $(".tanggal_rapat").datepicker({
                todayBtn   :'linked',
                changeYear : true,
                yearRange  : '+0:+1',
                dateFormat : 'yy-mm-dd',
                orientation: 'auto',
                autoclose  : true,
                minDate: '0',
            });
          
            meetingMaterialsTable();

            function meetingMaterialsTable() {
                $("#meeting-materials-table").html("");
                var id = $('.id_jadwal_rapat').attr('id');

                $.ajax({
                    type: "GET",
                    url: "/jadwal-rapat/"+id+"/edit",
                    success: function (response) {
                        // Get all data meeting materials from response json
                        $.each(response.meetingMaterials, function (key, value) {
                            $("#meeting-materials-table").append(
                                '<tr>\
                                    <td class="text-center">' +
                                    (key + 1) +
                                    "</td>\
                                    <td>" +
                                    value.nmr_sni_lama +
                                    "</td>\
                                    <td>" +
                                    value.jdl_sni_lama +
                                    '</td>\
                                    <td class="text-center">\
                                        <button id="' +
                                    value.id +
                                    '" class="btn btn-sm bg-gradient-danger mr-2 delete" title="Hapus" data-toggle="modal" data-target="#modal-delete-profile"><i class="fas fa-trash"></i></button>\
                                    </td>\
                                </tr>'
                            );
                        });
                    },
                });
            }

            $('#tombol-tambah-sni-lama').on('click', function() {
                $('#modal-sni-lama').modal('show');

                $.ajax({
                    type: "GET",
                    url: "/jadwal-rapat/data-sni-lama",
                    dataType: "JSON",
                    success: function (response) {
                        $.each(response.data_sni_lama, function (key, item) { 
                            $('#tabel-modal-sni-lama').append(
                                '<tr class="record">\
                                    <td style="width:20%">'+ item.nmr_sni_lama +'</td>\
                                    <td style="width:30%">'+ item.jdl_sni_lama +'</td>\
                                    <td style="width:25%">'+ item.komtek +'</td>\
                                    <td class="text-center" style="width:25%"><a href="javascript:void(0)" class="lihat-penerap" id="'+item.id+'">'+ item.jumlah_penerap +'</a></td>\
                                    <td>\
                                        <button type="button" value="'+item.id+'" class="btn btn-default tambah" title="Tambah data-sni-lama">\
                                            <i class="fas fa-plus"></i>\
                                        </button>\
                                    </td>\
                                </tr>'
                            );
                        });
                    }
                });


                //******************* Lihat Penerap **********************//
                $('#modal-sni-lama').on('click','.lihat-penerap',function() {
                    $('#modal-lihat-penerap').modal('show');

                    var id = $(this).attr('id');

                    $.ajax({
                        type: "GET",
                        url: "/jadwal-rapat/data-penerap/"+id,
                        dataType: "JSON",
                        success: function (response) {
                            $.each(response.data_penerap, function (key, item) {
                                $('#body-lihat-penerap').append('<p>'+item.penerap+'</p>');
                            });
                        }
                    })
                });

                $('#tutup-modal-lihat-penerap').click(function() {
                    $('#body-lihat-penerap').html('');
                });
            });

            $('#tutup-modal-sni-lama').on('click', function() {
                $('#tabel-modal-sni-lama').html('');
            });

            $('#modal-sni-lama').off('click').on('click', '.tambah', function() {
                $(this).closest('.record').remove();
                var id_sni_lama = $(this).val();

                $('.tambah').html('<i class="spinner-border spinner-border-sm text-dark" role="status"></i>');
                $('.tambah').attr('disabled', true);

                Toast.fire({
                    icon : "info",
                    title: "Mohon menunggu.",
                });
                
                $.ajax({
                type : "POST",
                data : {
                    id_jadwal_rapat : $('.id_jadwal_rapat').attr('id'),
                },
                url : "/jadwal-rapat/"+id_sni_lama+"/edit/tambah-bahan-rapat",
                success : function(response) {
                    Toast.fire({
                        icon : "success",
                        title: "Berhasil menambahkan bahan rapat.",
                    });
                    $("#meeting-materials-table").html("");
                    meetingMaterialsTable();
                    $('.tambah').html('<i class="fas fa-plus"></i>');
                    $('.tambah').attr('disabled', false);
                }
                });
            });

            /**********************************************/
            /************ Update Jadwal Rapat *************/
            /**********************************************/
            $("#form-edit-bahan-rapat").on("submit").validate({
                rules: {
                    tanggal_rapat: {
                        required: true,
                    },
                },
                messages: {
                    tanggal_rapat: {
                        required: "Masukkan tanggal rapat.",
                    },
                },
                errorElement: "span",
                errorPlacement: function (error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".form-group").append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass("is-invalid");
                },
                submitHandler: function (form) {
                    var id = $(".id_jadwal_rapat").attr("id");
                    $("#tombol-update-jadwal-rapat").html(
                        '<i class="spinner-border spinner-border-sm text-light" role="status"></i>'
                    );
                    $("#tombol-update-jadwal-rapat").attr("disabled", true);
                    $.ajax({
                        type: "PUT",
                        data: {
                            pic_rapat: $('#pic_rapat').val(),
                            tanggal_rapat: $('#tanggal_rapat').val(),
                        },
                        url: "/jadwal-rapat/"+id+"/edit",
                        success: function (response) {
                            Toast.fire({
                                icon: "success",
                                title: "Jadwal rapat berhasil diubah.",
                            });
                        },
                        error: function (response) {
                            Toast.fire({
                                icon: "error",
                                title: "Koneksi internet terputus, gagal simpan data.",
                            });
                        },
                        complete: function (response) {
                            $("#tombol-update-jadwal-rapat").html('<i class="fas fa-floppy-disk"></i>');
                            $("#tombol-update-jadwal-rapat").attr("disabled", false);
                            window.location.replace("/jadwal-rapat");
                        },
                    });
                },
            });


            /**********************************************/
            /************* Hapus Jadwal Rapat *************/
            /**********************************************/
            $(document).on('click', '.delete', function() {
            $("#modal-konfirmasi-hapus-bahan-rapat").modal("show");
                var id = $(this).attr('id');

                $.ajax({
                    type: "GET",
                    url: "/jadwal-rapat/konfirmasi-hapus-bahan-rapat/"+id,
                    success: function (response) {
                        $('#body-konfirmasi-hapus-bahan-rapat').append(
                                '<input type="hidden" id="id_hapus_bahan_rapat">\
                                <h6>Apakah anda yakin menghapus bahan rapat <strong>'+ response.data.nmr_sni_lama +'</strong> ?</h6>'
                            );

                            $('#id_hapus_bahan_rapat').val(response.data.id);
                    },
                });
            });

            $('.tutup-modal-konfirmasi-hapus-bahan-rapat').on('click', function() {
                $('#body-konfirmasi-hapus-bahan-rapat').html('');
            });

            $('.tombol-hapus-bahan-rapat').on('click', function() {

                var id_hapus_bahan_rapat = $('#id_hapus_bahan_rapat').val();

                $(this).html('<i class="spinner-border spinner-border-sm text-light" role="status"></i> Menghapus...');
                $(this).attr('disabled', true);

                $.ajax({
                    type: "DELETE",
                    url: "/jadwal-rapat/hapus-bahan-rapat/"+id_hapus_bahan_rapat,
                    success: function(response) {
                        Toast.fire({
                            icon : "success",
                            title: "Berhasil hapus bahan rapat masa transisi SNI.",
                        });
                        $('.tombol-hapus-bahan-rapat').html('Hapus');
                    $('.tombol-hapus-bahan-rapat').attr('disabled', false);
                        $('#modal-konfirmasi-hapus-bahan-rapat').modal('hide');
                        $('#body-konfirmasi-hapus-bahan-rapat').html('');
                        $("#meeting-materials-table").html("");
                        meetingMaterialsTable();
                    }
                });
            });
        });  
    </script>    
@endpush