<footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <a class="link" id="logis-version" href="javascript:void(0)">LOGIS Version 1.1</a>
    </div>
    <strong>Copyright &copy; 2023 <a href="https://bsn.go.id">Badan Standardisasi Nasional</a>.</strong> All rights reserved.
</footer>

{{-- Modal Lihat Penerap --}}
<div class="modal fade" data-backdrop="static" id="modal-versi-logis">
  <form id="form-penerap">
      <div class="modal-dialog modal-dialog-scrollable">
          <div class="modal-content">
              <div class="modal-header">
                  <h4 class="modal-title">Log Perbaharuan Tanggal 30 Maret 2023</h4>
                  <button type="button" class="close" id="tutup-modal-lihat-penerap" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <table>
                  <tr class="align-top">
                    <td>1.</td>
                    <td>Perubahan struktur kolom pada tabel meeting_materials.</td>
                  </tr>
                  <tr class="align-top">
                    <td>2.</td>
                    <td>Penambahan fitur edit pada hasil rapat pembahasan.</td>
                  </tr>
                  <tr class="align-top">
                    <td>3.</td>
                    <td>Penambahan informasi tanggal rapat dan catatan rapat pada modal detail SK.</td>
                  </tr>
                </table>
              </div>
          </div>
          <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
  </form>
</div>
{{-- End of - Modal SNI Lama, Komtek, Penerap --}}

@push('js')
  <script type="text/javascript">
    $('#logis-version').click(function() {
      $('#modal-versi-logis').modal('show');
    });
  </script>
@endpush