@extends('adminlte::page')

@section('title', 'Recargar Créditos - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-credit-card"></i> Recargar Créditos con Tarjeta
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

                <!-- Custom Amount -->
                <div class="form-group">
                    <label for="custom-amount"><strong>O ingresa un monto personalizado:</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₡</span>
                        </div>
                        <input type="number" class="form-control" id="custom-amount" 
                               min="5000" max="500000" step="1000" placeholder="Mínimo ₡5,000, Máximo ₡500,000">
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Monto mínimo: ₡5,000 (~$10 USD), Máximo: ₡500,000 (~$1,000 USD)
                    </small>
                </div>

                <!-- Selected Amount Display -->
                <div class="alert alert-success d-none" id="selected-amount-display">
                    <div class="text-center">
                        <h5><strong>Monto Seleccionado: ₡<span id="selected-amount-text">0</span></strong></h5>
                        <p class="mb-0">Nuevos créditos: ₡<span id="new-balance-preview">0</span></p>
                    </div>
                </div>

                <!-- Stripe Elements Container -->
                <div id="stripe-elements-container" class="d-none">
                    <hr>
                    <h5><i class="fas fa-lock"></i> Información de la Tarjeta</h5>
                    <div id="card-element" class="form-control" style="height: 40px; padding: 10px;">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="card-errors" role="alert" class="text-danger mt-2"></div>
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

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Procesando...</span>
                </div>
                <p class="mt-2 mb-0">Procesando pago...</p>
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
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 10px;
    }
    
    .amount-btn.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    #card-element {
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    }
    
    #card-element.StripeElement--focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .StripeElement--invalid {
        border-color: #dc3545;
    }
    
    .btn:disabled {
        cursor: not-allowed;
    }
</style>
@stop

@section('js')
<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Stripe
    const stripe = Stripe('{{ config("services.stripe.key") }}');
    const elements = stripe.elements();
    
    // Create card element
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
        },
    });
    
    let selectedAmount = 0;
    let selectedStudentId = 0;
    let currentCredits = 0;
    let paymentIntent = null;

    // Mount card element when modal is shown
    $('#rechargeModal').on('shown.bs.modal', function() {
        cardElement.mount('#card-element');
        
        // Add additional event listeners for better validation
        cardElement.on('ready', function() {
            console.log('Card element ready');
            setTimeout(checkFormValidity, 100);
        });
        
        cardElement.on('focus', function() {
            setTimeout(checkFormValidity, 100);
        });
        
        cardElement.on('blur', function() {
            setTimeout(checkFormValidity, 100);
        });
    });

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
        
        $('#rechargeModal').modal('show');
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
        
        if (amount >= 5000 && amount <= 500000) {
            setSelectedAmount(amount);
        } else {
            hideAmountSelection();
        }
    });

    // Set selected amount and show stripe form
    function setSelectedAmount(amount) {
        selectedAmount = amount;
        const newBalance = currentCredits + amount;
        
        $('#selected-amount-text').text(new Intl.NumberFormat('es-CR').format(amount));
        $('#new-balance-preview').text(new Intl.NumberFormat('es-CR').format(newBalance));
        $('#selected-amount-display').removeClass('d-none');
        $('#stripe-elements-container').removeClass('d-none');
        $('#confirm-payment-btn').removeClass('d-none');
        
        // Check form validity after a short delay to ensure elements are ready
        setTimeout(checkFormValidity, 200);
    }

    function hideAmountSelection() {
        selectedAmount = 0;
        $('#selected-amount-display').addClass('d-none');
        $('#stripe-elements-container').addClass('d-none');
        $('#confirm-payment-btn').addClass('d-none').prop('disabled', true);
    }

    function resetForm() {
        selectedAmount = 0;
        $('.amount-btn').removeClass('active');
        $('#custom-amount').val('');
        $('#selected-amount-display').addClass('d-none');
        $('#stripe-elements-container').addClass('d-none');
        $('#confirm-payment-btn').addClass('d-none').prop('disabled', true);
        $('#card-errors').text('');
    }

    // Handle real-time validation
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
        // Update validation after card state changes
        setTimeout(checkFormValidity, 100);
    });

    function checkFormValidity() {
        // Check if card element is ready and valid
        const cardComplete = cardElement._complete || false;
        const cardEmpty = cardElement._empty || false;
        const cardError = cardElement._invalid || false;
        
        const cardValid = cardComplete && !cardEmpty && !cardError;
        const amountValid = selectedAmount >= 5000 && selectedAmount <= 500000;
        
        // Temporary debug logging - remove after testing
        console.log('Form validation check:', {
            cardComplete: cardComplete,
            cardEmpty: cardEmpty,
            cardError: cardError,
            cardValid: cardValid,
            amountValid: amountValid,
            selectedAmount: selectedAmount,
            buttonShouldBeEnabled: cardValid && amountValid
        });
        
        const shouldEnable = cardValid && amountValid;
        $('#confirm-payment-btn').prop('disabled', !shouldEnable);
        
        // Visual feedback
        if (shouldEnable) {
            $('#confirm-payment-btn').removeClass('btn-secondary').addClass('btn-success');
        } else {
            $('#confirm-payment-btn').removeClass('btn-success').addClass('btn-secondary');
        }
    }

    // Handle payment processing
    $('#confirm-payment-btn').click(function() {
        processPayment();
    });

    async function processPayment() {
        if (!selectedAmount || !selectedStudentId) {
            toastr.error('Datos de pago incompletos');
            return;
        }

        // Show loading
        $('#loadingModal').modal('show');
        $('#confirm-payment-btn').prop('disabled', true);

        try {
            // Create payment intent
            const response = await fetch('{{ route("parent.create-payment-intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    amount: selectedAmount,
                    student_id: selectedStudentId
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            // Confirm payment with Stripe
            const result = await stripe.confirmCardPayment(data.client_secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: '{{ Auth::user()->name }}',
                        email: '{{ Auth::user()->email }}'
                    },
                }
            });

            if (result.error) {
                throw new Error(result.error.message);
            }

            // Process successful payment
            await processSuccessfulPayment(result.paymentIntent.id);

        } catch (error) {
            $('#loadingModal').modal('hide');
            $('#confirm-payment-btn').prop('disabled', false);
            toastr.error('Error: ' + error.message);
        }
    }

    async function processSuccessfulPayment(paymentIntentId) {
        try {
            const response = await fetch('{{ route("parent.process-payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    payment_intent_id: paymentIntentId
                })
            });

            const data = await response.json();

            $('#loadingModal').modal('hide');

            if (data.success) {
                toastr.success(data.message);
                $('#rechargeModal').modal('hide');
                
                // Reload page to update credits
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(data.message);
            }

        } catch (error) {
            $('#loadingModal').modal('hide');
            toastr.error('Error al procesar el pago: ' + error.message);
        }
    }

    // Clean up when modal is hidden
    $('#rechargeModal').on('hidden.bs.modal', function() {
        cardElement.unmount();
    });
});
</script>
@stop