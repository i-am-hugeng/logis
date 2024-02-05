@extends('layouts.main', ['title' => 'Rapat Pembahasan'])

@section('content-header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
            <h1>Detail Rapat Pembahasan</h1>
            </div>
        </div>
    </div><!-- /.container-fluid -->
@endsection

@section('content')
    <input type="hidden" id="level" value="{{ Auth::user()->level }}">
    <input type="hidden" id="id_admin" value="{{ Auth::user()->id }}">

    <div class="card">
        <form id="form-finalisasi" enctype="multipart/form-data">
            <div class="card-header">
                <input type="hidden" class="id_jadwal_rapat" id="{{ $tanggalRapat->id }}">
                <h3 class="card-title">Rapat Tanggal : {{ $tanggalRapat->tanggal_rapat }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-4">
                    <thead>
                    <th class="text-center align-top" style="width: 3%">No</th>
                    <th class="text-center align-top" style="width: 15%">SNI Baru</th>
                    <th class="text-center align-top" style="width: 15%">SNI Lama</th>
                    <th class="text-center align-top" style="width: 15%">Komtek</th>
                    <th class="text-center align-top" style="width: 10%">Status</th>
                    <th class="text-center align-top" style="width: 10%">Batas Transisi</th>
                    <th class="text-center align-top" style="width: 25%">Catatan</th>
                    <th class="text-center align-top" style="width: 10%">Aksi</th>
                    </thead>
                    <tbody id="tabel-pembahasan">
                    </tbody>
                </table>
            <button id="finalisasi-pembahasan" type="button" class="btn bg-gradient-primary col-sm-2"><i class="fas fa-handshake"></i> Finalisasi Rapat</button>
            </div>
            <!-- /.card-body -->
        </form>
    </div>

    <!-- {{-- Modal Bahas --}} -->
    <div class="modal fade" id="modal-bahas">
        <form id="form-bahas" enctype="multipart/form-data">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title inline">
                            Data SNI Dibahas
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="tutupModalBahas()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="hidden" id="id_bahan_rapat">
                        </div>

                        <table class="mb-3">
                            <tr class="form-group">
                                <th class="col-sm-3 align-top">SNI Baru</th>
                                <td class="align-top">:</td>
                                <td class="col-sm-9 align-top reset-value" id="sni-baru"></td>
                            </tr>
                            <tr class="form-group">
                                <th class="col-sm-3 align-top">SNI Lama</th>
                                <td class="align-top">:</td>
                                <td class="col-sm-9 align-top reset-value" id="sni-lama"></td>
                            </tr>
                            <tr class="form-group">
                                <th class="col-sm-3 align-top">Komtek</th>
                                <td class="align-top">:</td>
                                <td class="col-sm-9 align-top reset-value" id="komtek"></td>
                            </tr>
                            <tr class="form-group">
                                <th class="col-sm-3 align-top">Jumlah Penerap</th>
                                <td class="align-top">:</td>
                                <td class="col-sm-9 align-top reset-value" id="jumlah-penerap"></td>
                            </tr>
                        </table>

                        <div class="form-group">
                          <select class="form-control status" style="width: 100%;"
                            name="status">
                            <option value="" selected>--Status SNI Lama--</option>
                            <option value="0">Pencabutan</option>
                            <option value="1">Transisi</option>
                          </select>
                        </div>
                        <div class="form-group">
                          <input type="date" class="form-control batas-transisi" name="batas_transisi" hidden />
                        </div>
                        <div class="form-group">
                          <textarea class="form-control catatan" name="catatan" rows="5" placeholder="Catatan rapat..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn bg-gradient-danger" data-dismiss="modal" onclick="tutupModalBahas()">Tutup</button>
                        <button type="submit" class="btn bg-gradient-primary simpan-bahas">Simpan</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
        </form>
        <!-- /.modal-dialog -->
    </div>
    <!-- {{-- End of - Modal Bahas --}} -->

    {{-- Modal Lihat Penerap --}}
    <div class="modal fade" data-backdrop="static" id="modal-lihat-penerap">
        <form id="form-penerap">
            <div class="modal-dialog modal-lg modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Data Penerap</h4>
                        <button type="button" class="close" id="tutup-modal-lihat-penerap" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="body-lihat-penerap" class="modal-body">

                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </form>
    </div>
    {{-- End of - Modal SNI Lama, Komtek, Penerap --}}
@endsection

@push('css')
    
@endpush

@push('js')
    <script>
        //tutup modal bahas
        function tutupModalBahas() {
            $('#modal-bahas').modal('hide');
            $('.reset-value').html('');
            $('.status').prop('selectedIndex',0);
            $('.batas-transisi').prop('hidden', true);
            $('.batas-transisi').val('');
            $('.catatan').val('');
        }

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

            tabelPembahasan();

            function tabelPembahasan() {
            $("#tabel-pembahasan").html("");
            var id = $('.id_jadwal_rapat').attr('id');

            $.ajax({
                type: "GET",
                url: "/rapat-pembahasan/konten-admin/detail/"+id,
                success: function (response) {
                    // Get all data meeting materials from response json
                    $.each(response.pembahasan, function (key, value) {
                        $("#tabel-pembahasan").append(
                            '<tr>\
                                <td class="text-center">' + (key + 1) + '</td>\
                                <td>' + value.nmr_sni_baru + ' ' + value.jdl_sni_baru + '</td>\
                                <td>' + value.nmr_sni_lama + ' ' + value.jdl_sni_lama + '</td>\
                                <td>' + value.komtek + '</td>\
                                <td>' + (value.status_sni_lama === null ? '' : (value.status_sni_lama === 0 ? 'Pencabutan' : 'Transisi')) + '</td>\
                                <td class="text-center">' + (value.batas_transisi === null ? '' : value.batas_transisi) + '</td>\
                                <td>' + (value.catatan === null ? '' : value.catatan) + '</td>\
                                <td class="text-center">\
                                    <button type="button" id="' + value.id + '" class="btn btn-sm bg-gradient-olive bahas" title="Bahas" data-toggle="modal" data-target="#modal-bahas"><i class="fas fa-pen-to-square"></i></button>\
                                </td>\
                            </tr>'
                        );
                    });
                },
            });
            }

            /**********************************************/
            /*********** Modal Data Pembahasan ************/
            /**********************************************/
            $(document).on('click', '.bahas', function() {
                var idMeetingMaterial = $(this).attr('id');

                $.ajax({
                    type: "GET",
                    url: "/rapat-pembahasan/konten-admin/detail/bahan-rapat/"+idMeetingMaterial,
                    success: function (response) {
                        $('#id_bahan_rapat').val(response.bahanRapat.id);
                        $('#sni-baru').html(response.bahanRapat.nmr_sni_baru+', '+response.bahanRapat.jdl_sni_baru);
                        $('#sni-lama').html(response.bahanRapat.nmr_sni_lama+', '+response.bahanRapat.jdl_sni_lama);
                        $('#komtek').html(response.bahanRapat.komtek);
                        $('#jumlah-penerap').html('<a href="javascript:void(0)" id="'+response.bahanRapat.id+'" class="lihat-penerap">'+response.bahanRapat.jumlah_penerap+'</a>');

                        if(response.bahanRapat.status_sni_lama != null) {
                            $('.status option[value="'+ response.bahanRapat.status_sni_lama +'"]').prop('selected', true);
                            $('.catatan').val(response.bahanRapat.catatan);
                            if(response.bahanRapat.status_sni_lama == 1) {
                                $('.batas-transisi').attr('hidden', false);
                                $('.batas-transisi').val(response.batasTransisi.batas_transisi);
                            }
                        }
                    },
                });

                //menampilkan input date batas_transisi
                $('.status').on('change',function() {
                    if($(this).val() == 1) {
                        $('.batas-transisi').prop('hidden', false);
                    }
                    else {
                        $('.batas-transisi').prop('hidden', true);
                        $('.batas-transisi').val('');
                        $('.batas-transisi').removeClass('is-invalid');
                    }
                });

                tutupModalBahas();
            });

            /**********************************************/
            /*************** Lihat Penerap ****************/
            /**********************************************/
            $('body').on('click','.lihat-penerap',function() {
                $('#modal-lihat-penerap').modal('show');

                var id = $(this).attr('id');

                $.ajax({
                    type: "GET",
                    url: "/rapat-pembahasan/data-penerap/"+id,
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

            /**********************************************/
            /*********** Simpan Data Pembahasan ***********/
            /**********************************************/
            $("#form-bahas").on('submit', function() {
                if($('.batas-transisi').prop('hidden') === false) {
                    $('.batas-transisi').rules('add',
                        {
                            required: true,
                            messages: {
                                required: "Masukkan batas masa transisi.",
                            }
                        }
                    )
                }
            }).validate({
                rules: {
                    status: {
                        required: true,
                    },
                },
                messages: {
                    status: {
                        required: "Pilih status sni lama.",
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
                submitHandler: function(form) {
                    var id = $('#id_bahan_rapat').val();

                    $('.simpan-bahas').html('<i class="spinner-border spinner-border-sm text-light" role="status"></i> Menyimpan...');
                    $('.simpan-bahas').attr('disabled', true);

                    $.ajax({
                        type        : "POST",
                        dataType    : "JSON",
                        url         : "/rapat-pembahasan/konten-admin/detail/"+id+"/simpan",
                        contentType : false,
                        processData : false,
                        cache       : false,
                        data        : new FormData($(form)[0]),
                        success: function(response) {
                            Toast.fire({
                                icon : "success",
                                title: "Data pembahasan berhasil disimpan.",
                            });
                        },
                        error: function(response) {
                            Toast.fire({
                                icon : "error",
                                title: "Gagal simpan pembahasan.",
                            });
                        },
                        complete: function(response) {
                            tutupModalBahas();
                            tabelPembahasan();
                            $('.simpan-bahas').text('Simpan');
                            $('.simpan-bahas').attr('disabled', false);
                        }
                    });
                }
            });

            /**********************************************/
            /*********** Finalisasi Pembahasan ************/
            /**********************************************/
            $("#form-finalisasi").on('click', '#finalisasi-pembahasan', function() {
                Swal.fire({
                    title: 'Finalisasi rapat pembahasan?',
                    showDenyButton: false,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        actions: 'my-actions',
                        cancelButton: 'order-1 right-gap',
                        confirmButton: 'order-2',
                        denyButton: 'order-3',
                    }
                }).then((result) => {
                    if(result.isConfirmed) {
                        $('#finalisasi-pembahasan').html('<i class="spinner-border spinner-border-sm text-light" role="status"></i> Menyimpan...');
                        $('#finalisasi-pembahasan').attr('disabled', true);

                        $.ajax({
                            type : "POST",
                            data : {
                                id_rapat: $('.id_jadwal_rapat').attr('id'),
                            },
                            url : "/rapat-pembahasan/finalisasi-pembahasan",
                            success: function(response) {
                                Toast.fire({
                                    icon : "success",
                                    title: "Rapat pembahasan telah dilakukan.",
                                });
                                
                                window.location.replace("/rapat-pembahasan");
                            },
                            error: function(response) {
                                console.clear();
                                Toast.fire({
                                    icon : "error",
                                    title: response.responseJSON.error,
                                });

                                $('#finalisasi-pembahasan').html('<i class="fas fa-handshake"></i> Finalisasi Rapat');
                                $('#finalisasi-pembahasan').attr('disabled', false);
                            },
                            // complete: function(response) {
                            //     window.location.replace("/rapat-pembahasan");
                            // }
                        });
                    }
                })
            });
        });
    </script>
@endpush