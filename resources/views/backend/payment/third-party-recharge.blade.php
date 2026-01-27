@extends('adminlte::master')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
@stop

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', 'login-page')

@section('body')
    <div class="login-box third-party-box">
        <div class="login-logo">
            <img src="https://greenland.ga/logo/logo2.png" alt="BeeBus" class="logo-img" style="opacity: .8">
        </div>
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title mb-0">
                    <i class="fas fa-credit-card mr-2"></i>
                    Recargar Créditos
                </h3>
            </div>
            <div class="card-body">
                <!-- Student Info -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-user-graduate mr-2"></i>Información del Estudiante</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nombre:</strong><br>
                            {{ $student->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Cédula:</strong><br>
                            {{ $student->cedula }}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Colegio:</strong><br>
                            {{ $student->colegio ?? 'No especificado' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Créditos Actuales:</strong><br>
                            <span class="badge badge-info badge-lg">₡{{ number_format($student->creditos, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payer Information -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payer_name"><strong>Nombre *</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="payer_name" name="payer_name"
                                       placeholder="Ej: Juan" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payer_lastname"><strong>Apellido *</strong></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                </div>
                                <input type="text" class="form-control" id="payer_lastname" name="payer_lastname"
                                       placeholder="Ej: Pérez" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="payer_email"><strong>Correo electrónico *</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" id="payer_email" name="payer_email"
                               placeholder="correo@ejemplo.com" required>
                    </div>
                    <small class="form-text text-muted">Recibirá la confirmación del pago en este correo</small>
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
                        <p class="mb-0">Nuevos créditos del estudiante: ₡<span id="new-balance-preview">0</span></p>
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

                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <a href="{{ route('third-party.search') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Buscar Otro Estudiante
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-block d-none" id="confirm-payment-btn" disabled>
                            <i class="fas fa-credit-card mr-2"></i>
                            Procesar Pago
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('login') }}" class="btn btn-link">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PayMe Form (Hidden) -->
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
                    <h4 class="modal-title">
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
                    <p>El servicio de recarga de créditos permite a terceros agregar fondos a la cuenta de un estudiante para el uso del servicio de transporte escolar.</p>

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
                        <li>Verificar que está recargando al estudiante correcto</li>
                        <li>Guardar el comprobante de pago para cualquier consulta futura</li>
                    </ul>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Al marcar la casilla de aceptación, usted confirma que ha leído, entendido y acepta estos términos y condiciones.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="accept-terms-from-modal">
                        <i class="fas fa-check"></i> Acepto
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

@section('adminlte_js')
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- PayMe Modal JS -->
    <script type="text/javascript" src="https://integracion.alignetsac.com/VPOS2/js/modalcomercio.js"></script>

    <script>
        $(document).ready(function() {
            let selectedAmount = 0;
            const currentCredits = {{ $student->creditos }};
            const studentId = {{ $student->id }};

            // Configure toastr
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 8000,
                extendedTimeOut: 3000
            };

            // Show session messages
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif

            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif

            @if(session('info'))
                toastr.info('{{ session('info') }}');
            @endif

            // Show terms modal on page load
            $('#termsModal').modal('show');

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

            // Handle terms link click
            $('#terms-link').click(function(e) {
                e.preventDefault();
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
            });

            // Update payment button state
            function updatePaymentButton() {
                const termsAccepted = $('#accept-terms').is(':checked');
                const amountValid = selectedAmount >= 500 && selectedAmount <= 6500;
                const payerName = $('#payer_name').val().trim();
                const payerLastname = $('#payer_lastname').val().trim();
                const payerEmail = $('#payer_email').val().trim();

                if (termsAccepted && amountValid && payerName && payerLastname && payerEmail) {
                    $('#confirm-payment-btn').prop('disabled', false);
                } else {
                    $('#confirm-payment-btn').prop('disabled', true);
                }
            }

            // Validate payer info on input
            $('#payer_name, #payer_lastname, #payer_email').on('input', function() {
                updatePaymentButton();
            });

            // Handle payment processing
            $('#confirm-payment-btn').click(function(e) {
                e.preventDefault();
                processPayment();
            });

            function processPayment() {
                const payerName = $('#payer_name').val().trim();
                const payerLastname = $('#payer_lastname').val().trim();
                const payerEmail = $('#payer_email').val().trim();

                if (!selectedAmount || !studentId || !payerName || !payerLastname || !payerEmail) {
                    toastr.error('Por favor complete todos los campos requeridos');
                    return;
                }

                // Validate email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(payerEmail)) {
                    toastr.error('Por favor ingrese un correo electrónico válido');
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
                    url: '{{ route("third-party.payme.initialize") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: JSON.stringify({
                        amount: selectedAmount,
                        student_id: studentId,
                        payer_name: payerName,
                        payer_lastname: payerLastname,
                        payer_email: payerEmail
                    }),
                    contentType: 'application/json',
                    success: function(data) {
                        if (!data.success) {
                            throw new Error(data.message);
                        }

                        // Populate PayMe form
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

                        // Hide loading modal
                        $('#loadingModal').modal('hide');

                        console.log('Third Party PayMe Data:', paymeData);

                        // Verify AlignetVPOS2 is loaded
                        if (typeof AlignetVPOS2 === 'undefined') {
                            toastr.error('Error: Librería PayMe no cargada');
                            $('#confirm-payment-btn').prop('disabled', false);
                            return;
                        }

                        // Open PayMe modal
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

    <style>
        .third-party-box {
            width: 100%;
            max-width: 600px;
            padding: 0 15px;
            margin: 20px auto;
        }

        .logo-img {
            max-width: 100%;
            height: auto;
            max-height: 130px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 10px;
        }

        .amount-btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .badge-lg {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
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

        #terms-section a {
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
        }

        #terms-section a:hover {
            color: #0056b3;
        }

        /* Responsive Styles */
        @media (max-width: 576px) {
            .third-party-box {
                padding: 0 10px;
                margin-top: 10px;
            }

            .logo-img {
                max-height: 80px;
                width: auto;
            }

            .card-header h3.card-title {
                font-size: 0.95rem;
            }

            .card-body {
                padding: 1rem;
            }

            /* Student Info Section */
            .alert-info h5 {
                font-size: 1rem;
            }

            .alert-info .row .col-md-6 {
                margin-bottom: 8px;
            }

            .badge-lg {
                font-size: 0.95rem;
                padding: 0.4rem 0.8rem;
            }

            /* Payer Info Fields */
            .form-group label {
                font-size: 0.9rem;
            }

            .input-group .form-control {
                font-size: 0.9rem;
            }

            /* Amount Buttons */
            .amount-btn {
                font-size: 0.85rem;
                padding: 0.5rem 0.25rem;
            }

            /* Selected Amount Display */
            #selected-amount-display h5 {
                font-size: 1.1rem;
            }

            #selected-amount-display p {
                font-size: 0.9rem;
            }

            /* Action Buttons */
            .row.mt-3 .col-md-6 {
                margin-bottom: 10px;
            }

            .btn-block {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            /* Terms Section */
            #terms-section .custom-control-label {
                font-size: 0.9rem;
            }

            /* Terms Modal */
            #termsModal .modal-dialog {
                margin: 10px;
            }

            #termsModal .modal-body {
                padding: 1rem;
                max-height: 60vh;
            }

            #termsModal h5 {
                font-size: 1rem;
            }

            #termsModal p, #termsModal li {
                font-size: 0.9rem;
            }

            #termsModal .modal-footer {
                padding: 0.75rem;
                flex-wrap: wrap;
                gap: 10px;
            }

            #termsModal .modal-footer .btn {
                flex: 1;
                min-width: 120px;
            }
        }

        @media (max-width: 400px) {
            .logo-img {
                max-height: 60px;
            }

            .amount-btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.2rem;
            }

            .card-header h3.card-title {
                font-size: 0.85rem;
            }
        }
    </style>
@stop
