@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Histories Qr ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Alumnos</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Añadir o Actualizar</h3>
        </div>

        {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
        {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

        <div class="card-body">

				<div class="form-group row">
					<div class="col-sm-2 col-form-label">
						<strong class="field-title">Nombre</strong>
					</div>
					<div class="col-sm-10 col-content">
						{{ Form::text('name', $data->name, array('class' => 'form-control', 'required')) }}
						<small class="form-text text-muted">
							<i class="fa fa-question-circle" aria-hidden="true"></i> Nombre del Estudiante o Usuario.
						</small>
					</div>
				</div>
				
				<br>
				
				<div class="form-group row"> 
                    <div class="col-sm-2 col-form-label">
                        <strong class="field-title">Cédula</strong>
                    </div>
                    <div class="col-sm-10 col-content">
                        {{ Form::text('cedula', $data->cedula, array('class' => 'form-control', 'required')) }}
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Sin puntos ni guiones
                        </small>
                    </div>
                </div>
				
				<br>
				
				<div class="form-group row"> 
                    <div class="col-sm-2 col-form-label">
                        <strong class="field-title">Email</strong>
                    </div>
                    <div class="col-sm-10 col-content">
                        {{ Form::email('email', $data->email, array('class' => 'form-control', 'required')) }}
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                        </small>
                    </div>
                </div>
				
				<br>
                
                <div class="form-group row"> 
                    <div class="col-sm-2 col-form-label">
                        <strong class="field-title">Tipo de Beca</strong>
                    </div>
                    <div class="col-sm-10 col-content">
                           <select class="form-control" id="tipoBeca" name="tipoBeca">
                            <option value="">Tipo de Beca</option>
                                <option value="B-G (Beca Grenland)" {{($data->tipoBeca == 'B-G (Beca Grenland)') ? 'selected' : ''}} >B-G (Beca Grenland)</option>
                                <option value="B-M (Beca Mep)" {{($data->tipoBeca == 'B-M (Beca Mep)') ? 'selected' : ''}} >B-M (Beca Mep)</option>
                                <option value="P-G (Paga)" {{($data->tipoBeca == 'P-G (Paga)') ? 'selected' : ''}} >P-G (Paga)</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Tipo de Beca
                        </small>
                    </div>
                </div>
                
                <br>

                <div class="form-group row"> 
                    <div class="col-sm-2 col-form-label">
                        <strong class="field-title">Créditos Actuales</strong>
                    </div>
                    <div class="col-sm-10 col-content">
                        {{ Form::text('creditos', $data->creditos, array('class' => 'form-control', 'required')) }}
                        <small class="form-text text-muted">
                            <i class="fa fa-question-circle" aria-hidden="true"></i> Créditos actuales
                        </small>
                    </div>
                </div>
				
				<br>
				
				<div class="form-group row"> 
    				<div class="col-sm-2 col-form-label">
    					<strong class="field-title">¿Cuanto Restar por Asistencia?</strong>
    				</div>
    				<div class="col-sm-10 col-content">
    					{{ Form::text('cuantoRestar', $data->cuantoRestar, array('class' => 'form-control', 'required')) }}
    					<small class="form-text text-muted">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> Según tipo de Beca
                        </small>
    				</div>
				</div>
				
				<br>
				
				<div class="form-group row">
    				<div class="col-sm-2 col-form-label">
    					<strong class="field-title">¿Chances para marcar sin creditos?</strong>
    				</div>
    				<div class="col-sm-10 col-content">
    					{{ Form::text('chancesParaMarcar', $data->chancesParaMarcar, array('class' => 'form-control', 'required')) }}
    					<small class="form-text text-muted">
                        </small>
    				</div>
				</div>

                {{ Form::hidden('paradero_id', $data->paradero_id ?? null, array('id' => 'paradero_id')) }}

        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>
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

@stop

@section('js')
    <script>var typePage = "{{ $data->page_type }}";</script>

    <script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
