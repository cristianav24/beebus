@extends('adminlte::page')
<!-- page title -->
@section('title', 'Recargar Créditos | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Recargar Créditos</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recarga de Créditos para Usuario</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}

        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Cédula del Usuario</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::text('cedula', $data->cedula, array('class' => 'form-control', 'required', 'placeholder' => 'Ingrese la cédula del usuario')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Cédula del usuario al que se le recargarán los créditos.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Cantidad</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::number('amount', $data->amount, array('class' => 'form-control', 'required', 'min' => '1', 'max' => '10000', 'step' => '1')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Ingrese la cantidad de créditos a recargar (mínimo 1, máximo 10,000).
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Método de Pago</strong>
                </div>
                <div class="col-sm-10 col-content">
                    {{ Form::select('payment_method', [
                        '' => 'Seleccione un método de pago',
                        'transfer' => 'Transferencia Bancaria',
                        'card' => 'Tarjeta de Crédito/Débito',
                        'paypal' => 'PayPal'
                    ], $data->payment_method, array('class' => 'form-control', 'required')) }}
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Seleccione el método de pago utilizado.
                    </small>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-2 col-form-label">
                    <strong class="field-title">Comprobante de Pago</strong>
                </div>
                <div class="col-sm-10 col-content">
                    <input class="custom-file-input" name="payment_receipt" type="file"
                           accept="image/gif,image/jpeg,image/jpg,image/png,application/pdf" required>
                    <label class="custom-file-label" for="customFile">Seleccione archivo</label>
                    <small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Suba el comprobante de pago (JPG, PNG, PDF - máx. 5MB).
                    </small>
                </div>
            </div>

            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Información:</h5>
                Los créditos se añadirán inmediatamente a la cuenta del usuario especificado una vez procesada la recarga. 
                Asegúrese de ingresar la cédula correcta y subir un comprobante de pago válido antes de proceder.
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-credits-submit"
                            class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> {{ $data->button_text }}
                    </button>
                    <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <!-- /.card -->
    </div>
    <!-- /.row -->
    <!-- /.content -->
@stop

@section('css')
    <style>
        .top20 {
            margin-top: 20px;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Update file input label when file is selected
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });
            
            // Form validation
            $('#btn-credits-submit').click(function(e) {
                var cedula = $('input[name="cedula"]').val();
                var amount = $('input[name="amount"]').val();
                var paymentMethod = $('select[name="payment_method"]').val();
                var paymentReceipt = $('input[name="payment_receipt"]')[0].files.length;
                
                if (!cedula || cedula.trim() === '') {
                    e.preventDefault();
                    alert('Por favor ingrese la cédula del usuario.');
                    return false;
                }
                
                if (!amount || amount < 1) {
                    e.preventDefault();
                    alert('Por favor ingrese una cantidad válida de créditos.');
                    return false;
                }
                
                if (!paymentMethod) {
                    e.preventDefault();
                    alert('Por favor seleccione un método de pago.');
                    return false;
                }
                
                if (paymentReceipt === 0) {
                    e.preventDefault();
                    alert('Por favor suba el comprobante de pago.');
                    return false;
                }
                
                // Confirmation dialog
                if (!confirm('¿Está seguro de recargar ' + amount + ' créditos para el usuario con cédula ' + cedula + ' usando ' + $('select[name="payment_method"] option:selected').text() + '?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@stop