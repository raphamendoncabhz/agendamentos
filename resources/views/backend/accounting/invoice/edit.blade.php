@extends('layouts.app')

@section('content')
<link href="{{ asset('public/backend/plugins/bootstrap-select/css/bootstrap-select.css') }}" rel="stylesheet">

<div class="row">
	<div class="col-12">
		<div class="card">
			<span class="d-none panel-title">{{ _lang('Update Invoice') }}</span>

			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ action('InvoiceController@update', $id) }}" enctype="multipart/form-data">
					{{ csrf_field()}}
					<input name="_method" type="hidden" value="PATCH">				
					
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Number') }}</label>						
								<input type="text" class="form-control" name="invoice_number" value="{{ $invoice->invoice_number }}" required>
							</div>
						</div>


						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Related To') }}</label>						
								<select class="form-control select2 auto-select" data-selected="{{ $invoice->related_to }}" name="related_to" id="related_to">
								   <option value="contacts">{{ _lang('Customer') }}</option>
								   <option value="projects">{{ _lang('Project') }}</option>
								</select>
							</div>
						</div>
				
						<div class="col-md-4 {{ $invoice->related_to == 'contacts' ? '' : 'd-none' }}" id="contacts">
							<div class="form-group">
								<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
								<label class="control-label">{{ _lang('Select Client') }}</label>						
								<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-table="contacts" data-where="1" name="client_id" id="client_id">
									<option value="">{{ _lang('Select One') }}</option>
									{{ create_option("contacts","id","contact_name", $invoice->related_id, array("company_id="=>company_id())) }}
								</select>
							</div>
						</div>

						<div class="col-md-4 {{ $invoice->related_to == 'projects' ? '' : 'd-none' }}" id="projects">
							<div class="form-group">
								<label class="control-label">{{ _lang('Select Project') }}</label>						
								<select class="form-control select2" id="project_id" name="project_id">
								   <option value="">{{ _lang('Select One') }}</option>
								   {{ create_option('projects','id','name', $invoice->related_id, array('company_id=' => company_id())) }}
								</select>
							</div>
						</div>
				
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Date') }}</label>						
								<input type="text" class="form-control datepicker" name="invoice_date" value="{{ $invoice->invoice_date }}" required>
							</div>
						</div>
				
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Due Date') }}</label>						
								<input type="text" class="form-control datepicker" name="due_date" value="{{ $invoice->due_date }}" required>
							</div>
						</div>
						
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">{{ _lang('Invoice Template') }}</label>						
								<select class="form-control select2" name="template">
								   @foreach(get_invoice_templates() as $key => $value)
										<option value="{{ $key }}" {{ $invoice->template == $key ? 'selected' : '' }}>{{ $value }}</option>
								   @endforeach
								</select>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="form-group select-product-container">
								<a href="{{ route('products.create') }}" data-reload="false" data-title="{{ _lang('Add Product') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
								<label class="control-label">{{ _lang('Select Product') }}</label>						
								<select class="form-control select2-ajax" data-value="id" data-display="item_name" data-table="items" data-where="2" name="product" id="product">
									<option value="">{{ _lang('Select Product') }}</option>
								</select>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="form-group select-product-container">
								<a href="{{ route('services.create') }}" data-reload="false" data-title="{{ _lang('Add Service') }}" class="ajax-modal select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
								<label class="control-label">{{ _lang('Select Service') }}</label>						
								<select class="form-control select2-ajax" data-value="id" data-display="item_name" data-table="items" data-where="5" name="service" id="service">
									<option value="">{{ _lang('Select Service') }}</option>
								</select>
							</div>
						</div>
						
						<!--Order table -->
						@php $currency = currency(); @endphp
						
						@php $taxes = App\Tax::where("company_id",company_id())->get(); @endphp
						
						<div class="col-md-12">
							<div class="table-responsive">
								<table id="order-table" class="table table-bordered">
									<thead>
										<tr>
											<th>{{ _lang('Name') }}</th>
											<th>{{ _lang('Description') }}</th>
											<th class="text-center wp-100">{{ _lang('Quantity') }}</th>
											<th class="text-right">{{ _lang('Unit Cost').' '.$currency }}</th>
											<th class="text-right wp-100">{{ _lang('Discount').' '.$currency }}</th>
											<th>{{ _lang('Tax') }}</th>
											<th class="text-right">{{ _lang('Sub Total').' '.$currency }}</th>
											<th class="text-center">{{ _lang('Action') }}</th>
										</tr>
									</thead>
									<tbody>
										@foreach($invoice->invoice_items as $item)
											<tr id="product-{{ $item->item_id }}">
												<td>
													<b>{{ $item->item->item_name }}</b><br>
												</td>
												<td class="description"><input type="text" name="product_description[]" class="form-control input-description" value="{{ $item->description }}"></td>
												@if($item->item->item_type == 'product')
												<td class="text-center quantity"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center" value="{{ $item->quantity }}" max="{{ 1 + $item->item->product->stock->quantity }}"></td>
												@else
												<td class="text-center quantity"><input type="number" name="quantity[]" min="1" class="form-control input-quantity text-center" value="{{ $item->quantity }}"></td>
												@endif
												<td class="text-right unit-cost"><input type="text" name="unit_cost[]" class="form-control input-unit-cost text-right" value="{{ $item->unit_cost }}"></td>
												<td class="text-right discount"><input type="text" name="discount[]" class="form-control input-discount text-right" value="{{ $item->discount }}"></td>
												<td class="text-right tax">
													<select class="form-control auto-multiple-select selectpicker input-tax" name="tax[{{ $item->item_id }}][]" title="{{ _lang('Select TAX') }}" data-selected="{{ $item->taxes->pluck('tax_id') }}" multiple="true">
														@foreach($taxes as $tax)
															<option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
														@endforeach
													</select>
												</td>
												<td class="text-right sub-total"><input type="text" name="sub_total[]" class="form-control input-sub-total text-right" value="{{ $item->sub_total }}" readonly></td>
												<td class="text-center">
													<button type="button" class="btn btn-danger btn-xs remove-product"><i class='fa fa-trash'></i></button>
												</td>
												<input type="hidden" name="product_id[]" value="{{ $item->item_id }}">
												<input type="hidden" name="product_tax[]" class="input-product-tax" value="{{ $item->tax_amount }}">
											</tr>
										@endforeach
									</tbody>
									<tfoot class="tfoot active">
										<tr>
											<th>{{ _lang('Total') }}</th>
											<th></th>
											<th class="text-center" id="total-qty">0</th>
											<th class="text-right" id="total-discount">0.00</th>
											<th></th>
											<th class="text-right" id="total-tax">0.00</th>
											<th class="text-right" id="total">0.00</th>
											<th class="text-center"></th>
											<input type="hidden" name="product_total" id="product_total" value="0">
											<input type="hidden" name="tax_total" id="tax_total" value="0">
										</tr>
									</tfoot>
								</table>
								
								<table class="table table-striped">
								   <thead class="thead-light">
									  <tr>
										 <th>
											{{ _lang('Converted Amount') }} ({{ _lang('Client Currency') }} - <span class="client_currency">{{ base_currency() }}</span>)
											&emsp;<span id="converted_amount">{{ $currency }} 0.00</span>
										 </th>
									  </tr>
								   </thead>
								</table>	
							</div>
						</div>
				
						<!--End Order table -->
				
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Note') }}</label>						
								<textarea class="form-control" rows="4" name="note">{{ $invoice->note }}</textarea>
							</div>
						</div>
				
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<select class="form-control d-none" id="tax-selector">
	@foreach($taxes as $tax)
		<option value="{{ $tax->id }}" data-tax-type="{{ $tax->type }}" data-tax-rate="{{ $tax->rate }}">{{ $tax->tax_name }} - {{ $tax->type =='percent' ? $tax->rate.' %' : $tax->rate }}</option>
	@endforeach
</select>

@endsection
			
@section('js-script')
<script>
	var client_id = "{{ $invoice->client_id }}";
	var client_currency = "{{ $invoice->client->currency }}";
</script>

<script src="{{ asset('public/backend/plugins/bootstrap-select/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/backend/assets/js/invoice/edit.js?v=1.3') }}"></script>

@endsection
				  
				  
				  
				  