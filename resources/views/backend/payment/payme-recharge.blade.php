@extends('adminlte::page')

@section('title', 'Recargar Créditos - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-credit-card"></i> Recargar Créditos
    </h1>
    <div>
        <a href="{{ route('parent.payment-history') }}" class="btn btn-info">
            <i class="fas fa-history"></i> Historial de Pagos
        </a>
        <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Current Credits Summary -->
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-coins"></i> Créditos Actuales
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $children->count() }}</h3>
                        <p>Hijos Asignados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>₡{{ number_format($totalCredits, 0) }}</h3>
                        <p>Total Créditos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="avg-credits">₡{{ $children->count() > 0 ? number_format($totalCredits / $children->count(), 0) : 0 }}</h3>
                        <p>Promedio por Hijo</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Children Credits Detail -->
<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Detalles por Estudiante
        </h3>
    </div>
    <div class="card-body">
        @if($children->count() > 0)
        <div class="row">
            @foreach($children as $relationship)
            @if($relationship->student)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user"></i> {{ $relationship->student->name }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="text-center">
                            <h4 class="text-primary">
                                ₡{{ number_format($relationship->student->creditos, 0) }}
                            </h4>
                            <small class="text-muted">Créditos disponibles</small>
                            <br><br>
                            <button class="btn btn-success btn-sm recharge-btn"
                                data-student-id="{{ $relationship->student->id }}"
                                data-student-name="{{ $relationship->student->name }}"
                                data-current-credits="{{ $relationship->student->creditos }}">
                                <i class="fas fa-plus-circle"></i> Recargar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>No tienes hijos asignados.</strong>
            Contacta con el administrador para asignar estudiantes a tu cuenta.
        </div>
        @endif
    </div>
</div>

<!-- Recharge Modal -->
<div class="modal fade" id="rechargeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-credit-card"></i> Recargar Créditos - <span id="modal-student-name"></span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Student Info -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Estudiante:</strong> <span id="modal-student-info"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Créditos Actuales:</strong> ₡<span id="modal-current-credits"></span>
                        </div>
                    </div>
                </div>

                <!-- Amount Selection -->
                <div class="form-group">
                    <label><strong>Selecciona el monto a recargar:</strong></label>
                    <div class="row">
                        @foreach($predefinedAmounts as $amount => $label)
                        <div class="col-md-4 col-6 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block amount-btn"
                                data-amount="{{ $amount }}">
                                {{ $label }}
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Selected Amount Display -->
                <div class="alert alert-success d-none" id="selected-amount-display">
                    <div class="text-center">
                        <h5><strong>Monto Seleccionado: ₡<span id="selected-amount-text">0</span></strong></h5>
                        <p class="mb-0">Nuevos créditos: ₡<span id="new-balance-preview">0</span></p>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="d-none" id="terms-section">
                    <hr>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="accept-terms">
                            <label class="custom-control-label" for="accept-terms">
                                He leído y acepto los
                                <a href="#" id="terms-link">términos y condiciones</a>
                                de la recarga de créditos
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Debes aceptar los términos para continuar con el pago
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success d-none" id="confirm-payment-btn" disabled>
                    <i class="fas fa-credit-card"></i> Procesar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PayMe Form (Hidden) - Will be populated dynamically by JavaScript -->
<form name="f1" id="f1" action="#" method="post" class="alignet-form-vpos2">
    <input type="hidden" name="acquirerId" id="acquirerId">
    <input type="hidden" name="idCommerce" id="idCommerce">
    <input type="hidden" name="purchaseOperationNumber" id="purchaseOperationNumber">
    <input type="hidden" name="purchaseAmount" id="purchaseAmount">
    <input type="hidden" name="purchaseCurrencyCode" id="purchaseCurrencyCode">
    <input type="hidden" name="language" id="language" value="SP">
    <input type="hidden" name="shippingFirstName" id="shippingFirstName">
    <input type="hidden" name="shippingLastName" id="shippingLastName">
    <input type="hidden" name="shippingEmail" id="shippingEmail">
    <input type="hidden" name="shippingAddress" id="shippingAddress">
    <input type="hidden" name="shippingZIP" id="shippingZIP">
    <input type="hidden" name="shippingCity" id="shippingCity">
    <input type="hidden" name="shippingState" id="shippingState">
    <input type="hidden" name="shippingCountry" id="shippingCountry">
    <input type="hidden" name="userCommerce" id="userCommerce">
    <input type="hidden" name="userCodePayme" id="userCodePayme">
    <input type="hidden" name="descriptionProducts" id="descriptionProducts">
    <input type="hidden" name="programmingLanguage" id="programmingLanguage" value="PHP">
    <input type="hidden" name="reserved1" id="reserved1">
    <input type="hidden" name="purchaseVerification" id="purchaseVerification">
</form>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">purchaseAmount
                    <i class="fas fa-file-contract"></i> Términos y Condiciones de Recarga de Créditos
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <h5>1. Aceptación de los Términos</h5>
                <p>Al utilizar el servicio de recarga de créditos de BeeBus, usted acepta estar sujeto a estos términos y condiciones.</p>

                <h5>2. Descripción del Servicio</h5>
                <p>El servicio de recarga de créditos permite a los padres o tutores agregar fondos a la cuenta de sus hijos para el uso del servicio de transporte escolar.</p>

                <h5>3. Montos de Recarga</h5>
                <ul>
                    <li>Monto mínimo de recarga: ₡500 (quinientos colones)</li>
                    <li>Monto máximo de recarga: ₡6,500 (seis mil quinientos colones)</li>
                    <li>Los créditos no tienen fecha de vencimiento</li>
                </ul>

                <h5>4. Procesamiento de Pagos</h5>
                <ul>
                    <li>Los pagos se procesan a través de la pasarela segura PayMe/Alignet</li>
                    <li>Aceptamos tarjetas de crédito y débito Visa y Mastercard</li>
                    <li>El cargo aparecerá en su estado de cuenta como "BeeBus - Recarga Créditos"</li>
                </ul>

                <h5>5. Confirmación y Aplicación de Créditos</h5>
                <ul>
                    <li>Los créditos se aplican inmediatamente después de la aprobación del pago</li>
                    <li>Recibirá una confirmación en pantalla del resultado de la transacción</li>
                    <li>En caso de error, el sistema no aplicará los créditos</li>
                </ul>

                <h5>6. Política de Reembolsos</h5>
                <ul>
                    <li>Los créditos cargados son <strong>no reembolsables</strong></li>
                    <li>En caso de cargos duplicados por error del sistema, contacte al administrador</li>
                    <li>No se realizan reembolsos por créditos no utilizados</li>
                </ul>

                <h5>7. Uso de los Créditos</h5>
                <ul>
                    <li>Los créditos solo pueden usarse para pagar servicios de transporte escolar</li>
                    <li>Cada viaje consumirá el monto configurado por el administrador</li>
                    <li>El saldo de créditos puede consultarse en cualquier momento desde el panel</li>
                </ul>

                <h5>8. Seguridad y Privacidad</h5>
                <ul>
                    <li>No almacenamos información completa de tarjetas de crédito</li>
                    <li>Todos los datos sensibles son procesados por PayMe de forma segura</li>
                    <li>Sus datos personales están protegidos según nuestra política de privacidad</li>
                </ul>

                <h5>9. Responsabilidades del Usuario</h5>
                <ul>
                    <li>Verificar que los datos ingresados sean correctos antes de confirmar</li>
                    <li>Mantener seguras sus credenciales de acceso</li>
                    <li>Notificar inmediatamente cualquier actividad sospechosa en su cuenta</li>
                </ul>

                <h5>10. Modificaciones</h5>
                <p>BeeBus se reserva el derecho de modificar estos términos y condiciones en cualquier momento. Los cambios serán efectivos inmediatamente después de su publicación.</p>

                <h5>11. Contacto</h5>
                <p>Para consultas o problemas relacionados con recargas de créditos, contacte al administrador del sistema a través del panel de control.</p>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Al marcar la casilla de aceptación, usted confirma que ha leído, entendido y acepta estos términos y condiciones.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="accept-terms-from-modal">
                    <i class="fas fa-check"></i> Acepto y Continuar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Procesando...</span>
                </div>
                <p class="mt-2 mb-0">Iniciando pago...</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

<style>
    .small-box {
        border-radius: 10px;
    }

    .card {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border-radius: 10px;
    }

    .amount-btn.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .btn:disabled {
        cursor: not-allowed;
    }

    #terms-section a {
        color: #007bff;
        text-decoration: underline;
        cursor: pointer;
    }

    #terms-section a:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .custom-control-label {
        cursor: pointer;
        user-select: none;
    }

    #termsModal .modal-body {
        text-align: justify;
    }

    #termsModal h5 {
        color: #007bff;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    #termsModal ul {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- PayMe Modal JS - MUST be loaded before using AlignetVPOS2 -->
<script type="text/javascript" src="https://integracion.alignetsac.com/VPOS2/js/modalcomercio.js"></script>

<script>
    $(document).ready(function() {
        let selectedAmount = 0;
        let selectedStudentId = 0;
        let currentCredits = 0;

        // Handle recharge button click
        $('.recharge-btn').click(function() {
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const credits = $(this).data('current-credits');

            selectedStudentId = studentId;
            currentCredits = credits;

            $('#modal-student-name').text(studentName);
            $('#modal-student-info').text(studentName);
            $('#modal-current-credits').text(new Intl.NumberFormat('es-CR').format(credits));

            // Reset form
            resetForm();

            // Show terms modal first
            $('#termsModal').modal('show');
        });

        // Handle amount button clicks
        $('.amount-btn').click(function() {
            const amount = parseInt($(this).data('amount'));

            $('.amount-btn').removeClass('active');
            $(this).addClass('active');

            $('#custom-amount').val('');
            setSelectedAmount(amount);
        });

        // Handle custom amount input
        $('#custom-amount').on('input', function() {
            const amount = parseInt($(this).val()) || 0;

            $('.amount-btn').removeClass('active');

            if (amount >= 500 && amount <= 6500) {
                setSelectedAmount(amount);
            } else {
                hideAmountSelection();
            }
        });

        // Set selected amount
        function setSelectedAmount(amount) {
            selectedAmount = amount;
            const newBalance = currentCredits + amount;

            $('#selected-amount-text').text(new Intl.NumberFormat('es-CR').format(amount));
            $('#new-balance-preview').text(new Intl.NumberFormat('es-CR').format(newBalance));
            $('#selected-amount-display').removeClass('d-none');
            $('#terms-section').removeClass('d-none');
            $('#confirm-payment-btn').removeClass('d-none');

            // Reset terms checkbox
            $('#accept-terms').prop('checked', false);
            updatePaymentButton();
        }

        function hideAmountSelection() {
            selectedAmount = 0;
            $('#selected-amount-display').addClass('d-none');
            $('#terms-section').addClass('d-none');
            $('#confirm-payment-btn').addClass('d-none');
        }

        function resetForm() {
            selectedAmount = 0;
            $('.amount-btn').removeClass('active');
            $('#custom-amount').val('');
            $('#selected-amount-display').addClass('d-none');
            $('#terms-section').addClass('d-none');
            $('#confirm-payment-btn').addClass('d-none');
            $('#accept-terms').prop('checked', false);
        }

        // Handle terms link click
        $('#terms-link').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#termsModal').modal('show');
        });

        // Handle terms checkbox
        $('#accept-terms').change(function() {
            updatePaymentButton();
        });

        // Handle accept button in terms modal
        $('#accept-terms-from-modal').click(function() {
            $('#accept-terms').prop('checked', true);
            updatePaymentButton();
            $('#termsModal').modal('hide');
            // Open recharge modal after accepting terms
            $('#rechargeModal').modal('show');
        });

        // Handle close button in terms modal
        $('#termsModal .close, #termsModal .btn-secondary').click(function() {
            // If terms are not accepted when closing the modal, reset the selection
            if (!$('#accept-terms').is(':checked')) {
                selectedStudentId = 0;
                currentCredits = 0;
            }
        });

        // Update payment button state based on terms acceptance
        function updatePaymentButton() {
            const termsAccepted = $('#accept-terms').is(':checked');
            const amountValid = selectedAmount >= 500 && selectedAmount <= 6500;

            if (termsAccepted && amountValid) {
                $('#confirm-payment-btn').prop('disabled', false);
            } else {
                $('#confirm-payment-btn').prop('disabled', true);
            }
        }

        // Handle payment processing
        $('#confirm-payment-btn').click(function(e) {
            e.preventDefault();
            processPayment();
        });

        function processPayment() {
            if (!selectedAmount || !selectedStudentId) {
                toastr.error('Datos de pago incompletos');
                return;
            }

            // Verify terms acceptance
            if (!$('#accept-terms').is(':checked')) {
                toastr.error('Debes aceptar los términos y condiciones para continuar');
                return;
            }

            // Show loading
            $('#loadingModal').modal('show');
            $('#confirm-payment-btn').prop('disabled', true);

            // Initialize payment with PayMe
            $.ajax({
                url: '{{ route("parent.payme.initialize") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({
                    amount: selectedAmount,
                    student_id: selectedStudentId
                }),
                contentType: 'application/json',
                success: function(data) {
                    if (!data.success) {
                        throw new Error(data.message);
                    }

                    // Populate PayMe form with backend data
                    const paymeData = data.data;
                    $('#acquirerId').val(paymeData.acquirerId);
                    $('#idCommerce').val(paymeData.idCommerce);
                    $('#purchaseOperationNumber').val(paymeData.purchaseOperationNumber);
                    $('#purchaseAmount').val(paymeData.purchaseAmount);
                    $('#purchaseCurrencyCode').val(paymeData.purchaseCurrencyCode);
                    $('#shippingFirstName').val(paymeData.shippingFirstName);
                    $('#shippingLastName').val(paymeData.shippingLastName);
                    $('#shippingEmail').val(paymeData.shippingEmail);
                    $('#shippingAddress').val(paymeData.shippingAddress);
                    $('#shippingZIP').val(paymeData.shippingZIP);
                    $('#shippingCity').val(paymeData.shippingCity);
                    $('#shippingState').val(paymeData.shippingState);
                    $('#shippingCountry').val(paymeData.shippingCountry);
                    $('#userCommerce').val(paymeData.userCommerce);
                    $('#userCodePayme').val(paymeData.userCodePayme);
                    $('#descriptionProducts').val(paymeData.descriptionProducts);
                    $('#reserved1').val(paymeData.reserved1);
                    $('#purchaseVerification').val(paymeData.purchaseVerification);

                    // Hide loading and recharge modals
                    $('#loadingModal').modal('hide');
                    $('#rechargeModal').modal('hide');

                    console.log('PayMe Data:', paymeData);
                    console.log('PayMe URL:', paymeData.payme_url);

                    // DEBUG: Mostrar datos críticos
                    console.log('=== DATOS CRÍTICOS PARA PAYME ===');
                    console.log('acquirerId:', paymeData.acquirerId);
                    console.log('idCommerce:', paymeData.idCommerce);
                    console.log('purchaseOperationNumber:', paymeData.purchaseOperationNumber);
                    console.log('purchaseAmount:', paymeData.purchaseAmount);
                    console.log('purchaseCurrencyCode:', paymeData.purchaseCurrencyCode);
                    console.log('purchaseVerification (hash):', paymeData.purchaseVerification);
                    console.log('userCommerce:', paymeData.userCommerce);
                    console.log('userCodePayme:', paymeData.userCodePayme);
                    console.log('=================================');

                    // Verify AlignetVPOS2 is loaded
                    if (typeof AlignetVPOS2 === 'undefined') {
                        toastr.error('Error: Librería PayMe no cargada');
                        $('#confirm-payment-btn').prop('disabled', false);
                        return;
                    }

                    // IMPORTANT: Open modal directly from user action (no setTimeout)
                    // According to PayMe docs, Safari requires direct user interaction
                    try {
                        AlignetVPOS2.openModal(paymeData.payme_url);
                    } catch (e) {
                        console.error('Error opening PayMe modal:', e);
                        toastr.error('Error al abrir modal de pago: ' + e.message);
                        $('#confirm-payment-btn').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Payment processing error:', error);
                    $('#loadingModal').modal('hide');
                    $('#confirm-payment-btn').prop('disabled', false);

                    const message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message :
                        'Error al procesar el pago';

                    toastr.error('Error: ' + message);
                }
            });
        }
    });
</script>
@stop