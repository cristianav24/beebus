{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Accesos Qr | ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Accesos Qr</h1>
@stop

@section('content')
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">List</h3>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            {!! $html->table(['class' => 'table table-hover']) !!}
        </div>
    </div>
</div>
@stop

@section('css')
<link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
@stop

@section('js')
<!--Data tables-->
<script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/jszip/jszip.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/pdfmake/pdfmake.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/pdfmake/vfs_fonts.js') }}"></script>
{{--Button--}}
<script src="{{ asset('vendor/datatables-plugins/buttons/js/dataTables.buttons.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.flash.min.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.colVis.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.html5.js') }}"></script>
<script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.print.js') }}"></script>
{!! $html->scripts() !!}
<script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    function confirmInactive(history_id) {
        if (confirm(`Estas seguro de que deseas inactivar a este usuario? ${history_id}`)) {
            $.ajax({
                url: "{{ url('/histories/set-inactive') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: history_id
                },
                success: function(response) {
                    if (response.success) {
                        alert('Se actualizo correctamente.');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    }

    // Descargar QR al hacer click en el botón
    $(document).on('click', '.btn-qr-download', function() {
        var qrData = $(this).data('qr');
        var studentId = $(this).data('id');

        // Generar QR
        var qr = qrcode(0, 'M');
        qr.addData(qrData);
        qr.make();

        // Crear imagen y descargar
        var qrImage = qr.createDataURL(4);
        var link = document.createElement('a');
        link.href = qrImage;
        link.download = 'qr_estudiante_' + studentId + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>

@stop