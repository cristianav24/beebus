@extends('adminlte::page')

@section('title', 'Firmar Contrato - BeeBus')

@section('content_header')
    <h1><i class="fa fa-file-signature"></i> Firmar Contrato Digitalmente</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Contrato de Servicio BeeBus 2026</h3>
            </div>
            <div class="card-body p-0">
                <!-- Visor del PDF -->
                <div id="pdf-container" style="width: 100%; height: 60vh; background: #525659;">
                    <embed id="pdf-viewer" src="{{ asset('templates/Contrato_Bee_Bus_2026.pdf') }}" type="application/pdf" style="width: 100%; height: 100%;">
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="button" class="btn btn-lg btn-success" onclick="openSignatureModal()">
                    <i class="fa fa-pen"></i> Abrir Panel de Firma
                </button>
                <a href="{{ route('student.dashboard') }}" class="btn btn-lg btn-secondary">
                    <i class="fa fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Firma -->
<div id="signature-modal">
    <div class="sig-header">
        <div><i class="fa fa-pen"></i> <strong>{{ $student->full_name }}</strong></div>
        <div class="sig-cedula">{{ $student->cedula }}</div>
    </div>

    <div class="sig-canvas-container">
        <canvas id="signature-pad"></canvas>
    </div>

    <div class="sig-footer">
        <button type="button" class="btn btn-secondary" onclick="closeSignatureModal()">
            <i class="fa fa-times"></i> Cancelar
        </button>
        <button type="button" class="btn btn-warning" onclick="clearSignature()">
            <i class="fa fa-eraser"></i> Limpiar
        </button>
        <button type="button" id="btn-save-signature" class="btn btn-success" onclick="saveSignature()">
            <i class="fa fa-check"></i> Guardar
        </button>
    </div>
</div>
@stop

@section('css')
<style>
    /* Modal de firma - usa 100dvh para respetar viewport dinamico en moviles */
    #signature-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        height: 100dvh; /* viewport dinamico - respeta barra navegacion movil */
        background: #fff;
        z-index: 99999;
        flex-direction: column;
        overflow: hidden;
    }

    /* Header compacto */
    .sig-header {
        flex: 0 0 auto;
        padding: 10px;
        background: #007bff;
        color: white;
        text-align: center;
        font-size: 14px;
    }
    .sig-cedula {
        font-size: 11px;
        opacity: 0.9;
    }

    /* Canvas - usa el espacio restante */
    .sig-canvas-container {
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        display: flex;
        background: #e0e0e0;
        padding: 8px;
    }
    #signature-pad {
        width: 100%;
        height: 100%;
        background: white;
        border: 2px dashed #888;
        border-radius: 8px;
        cursor: crosshair;
        touch-action: none;
    }

    /* Footer siempre visible */
    .sig-footer {
        flex: 0 0 auto;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 12px 8px;
        background: #dee2e6;
        border-top: 2px solid #adb5bd;
        gap: 10px;
    }
    .sig-footer .btn {
        flex: 0 1 auto;
        min-width: 90px;
        padding: 12px 15px;
        font-size: 13px;
        font-weight: bold;
        white-space: nowrap;
    }

    /* Ajustes para landscape en movil */
    @media screen and (max-height: 450px) {
        .sig-header {
            padding: 6px 10px;
            font-size: 12px;
        }
        .sig-cedula {
            display: none;
        }
        .sig-canvas-container {
            padding: 5px;
        }
        .sig-footer {
            padding: 8px 5px;
        }
        .sig-footer .btn {
            min-width: 70px;
            padding: 10px 8px;
            font-size: 11px;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdf-lib/dist/pdf-lib.min.js"></script>

<script>
    const modal = document.getElementById('signature-modal');
    const canvas = document.getElementById('signature-pad');
    let signaturePad;
    let resizeTimeout = null;
    let isResizing = false;

    // Datos del estudiante
    const studentName = "{{ $student->full_name }}";
    const studentCedula = "{{ $student->cedula }}";

    // Funcion para convertir Uint8Array a base64 sin stack overflow
    function uint8ArrayToBase64(bytes) {
        let binary = '';
        const len = bytes.byteLength;
        const chunkSize = 8192; // Procesar en chunks para evitar stack overflow
        for (let i = 0; i < len; i += chunkSize) {
            const chunk = bytes.subarray(i, Math.min(i + chunkSize, len));
            binary += String.fromCharCode.apply(null, chunk);
        }
        return btoa(binary);
    }

    function initSignaturePad() {
        if (isResizing) return;
        isResizing = true;

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);

        if (!signaturePad) {
            signaturePad = new SignaturePad(canvas, {
                penColor: 'rgb(0, 0, 128)',
                minWidth: 1,
                maxWidth: 3
            });
        } else {
            signaturePad.clear();
        }

        drawGuideLine();
        isResizing = false;
    }

    // Forzar orientacion horizontal en moviles
    function lockLandscape() {
        if (screen.orientation && screen.orientation.lock) {
            screen.orientation.lock('landscape').catch(function(err) {
                console.log('No se pudo bloquear orientacion:', err.message);
            });
        }
    }

    function unlockOrientation() {
        if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
        }
    }

    function openSignatureModal() {
        modal.style.display = 'flex';

        // Intentar forzar orientacion horizontal
        lockLandscape();

        // Inicializar despues de que el modal sea visible
        setTimeout(function() {
            initSignaturePad();
        }, 150);
    }

    function closeSignatureModal() {
        modal.style.display = 'none';
        unlockOrientation();
    }

    function drawGuideLine() {
        if (!canvas.offsetWidth || !canvas.offsetHeight) return;

        const ctx = canvas.getContext("2d");
        const y = canvas.offsetHeight * 0.7;
        ctx.save();
        ctx.strokeStyle = '#ccc';
        ctx.lineWidth = 2;
        ctx.setLineDash([10, 5]);
        ctx.beginPath();
        ctx.moveTo(canvas.offsetWidth * 0.1, y);
        ctx.lineTo(canvas.offsetWidth * 0.9, y);
        ctx.stroke();

        // Texto guia
        ctx.fillStyle = '#999';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Firme sobre esta linea', canvas.offsetWidth / 2, y + 25);
        ctx.restore();
    }

    function clearSignature() {
        if (signaturePad) {
            signaturePad.clear();
            drawGuideLine();
        }
    }

    async function saveSignature() {
        if (!signaturePad || signaturePad.isEmpty()) {
            alert("Por favor, firme el documento antes de guardar.");
            return;
        }

        const btnSave = document.getElementById('btn-save-signature');
        btnSave.disabled = true;
        btnSave.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';

        try {
            // 1. Cargar el PDF template
            const pdfUrl = "{{ asset('templates/Contrato_Bee_Bus_2026.pdf') }}";
            const pdfBytes = await fetch(pdfUrl).then(res => res.arrayBuffer());
            const pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);

            // 2. Obtener la ultima pagina
            const pages = pdfDoc.getPages();
            const lastPage = pages[pages.length - 1];
            const { width, height } = lastPage.getSize();

            // 3. Embeber la imagen de la firma
            const base64Image = signaturePad.toDataURL('image/png');
            const base64Data = base64Image.split(',')[1];
            const sigBytes = Uint8Array.from(atob(base64Data), c => c.charCodeAt(0));
            const signatureImage = await pdfDoc.embedPng(sigBytes);

            // 4. Posicion de la firma (ajustar segun el PDF)
            const mmToPt = 2.835;
            const sigX = 60 * mmToPt;
            const sigW = 70 * mmToPt;
            const sigH = 30 * mmToPt;
            const sigY = height - 260 * mmToPt;

            lastPage.drawImage(signatureImage, {
                x: sigX,
                y: sigY,
                width: sigW,
                height: sigH,
            });

            // 5. Linea bajo la firma
            const lineX1 = 50 * mmToPt;
            const lineX2 = 160 * mmToPt;
            const lineY = height - 260 * mmToPt;

            lastPage.drawLine({
                start: { x: lineX1, y: lineY },
                end: { x: lineX2, y: lineY },
                thickness: 1,
                color: PDFLib.rgb(0, 0, 0),
            });

            // 6. Nombre del estudiante
            const font = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold);
            const fontSize = 10;
            const nameText = studentName.toUpperCase();
            const textWidth = font.widthOfTextAtSize(nameText, fontSize);
            const textX = (lineX1 + lineX2) / 2 - textWidth / 2;
            const textY = height - 268 * mmToPt;

            lastPage.drawText(nameText, {
                x: textX,
                y: textY,
                size: fontSize,
                font: font,
                color: PDFLib.rgb(0, 0, 0),
            });

            // Cedula
            const cedulaText = "Cedula: " + studentCedula;
            const cedulaWidth = font.widthOfTextAtSize(cedulaText, 9);
            lastPage.drawText(cedulaText, {
                x: (lineX1 + lineX2) / 2 - cedulaWidth / 2,
                y: textY - 12,
                size: 9,
                font: font,
                color: PDFLib.rgb(0.3, 0.3, 0.3),
            });

            // 7. Fecha de firma
            const today = new Date();
            const dateText = "Firmado digitalmente: " + today.toLocaleDateString('es-CR') + " " + today.toLocaleTimeString('es-CR');
            lastPage.drawText(dateText, {
                x: 50 * mmToPt,
                y: height - 280 * mmToPt,
                size: 8,
                font: await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica),
                color: PDFLib.rgb(0.5, 0.5, 0.5),
            });

            // 8. Guardar el PDF modificado - usando funcion que evita stack overflow
            const signedPdfBytes = await pdfDoc.save();
            const pdfBase64 = uint8ArrayToBase64(new Uint8Array(signedPdfBytes));

            // 9. Enviar al servidor Laravel
            const response = await fetch("{{ route('student.save-signature') }}", {
                method: 'POST',
                body: JSON.stringify({ pdf_signed: pdfBase64 }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.status === 'success') {
                alert(data.message);
                closeSignatureModal();
                window.location.href = data.redirect;
            } else {
                alert("Error: " + data.message);
            }

        } catch (err) {
            alert("Error al procesar la firma: " + err.message);
            console.error(err);
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="fa fa-check"></i> Guardar Firma';
        }
    }

    // Redimensionar canvas si cambia orientacion (con debounce)
    window.addEventListener("resize", function() {
        if (modal.style.display === 'flex') {
            if (resizeTimeout) clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                initSignaturePad();
            }, 200);
        }
    });

    // Manejar cambio de orientacion
    window.addEventListener("orientationchange", function() {
        if (modal.style.display === 'flex') {
            setTimeout(function() {
                initSignaturePad();
            }, 300);
        }
    });
</script>
@stop
