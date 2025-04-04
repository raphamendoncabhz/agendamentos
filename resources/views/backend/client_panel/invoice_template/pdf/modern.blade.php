<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ get_option('site_title', 'ElitKit Invoice') }}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style type="text/css">
@php include public_path('backend/assets/css/bootstrap.min.css') @endphp
@php include public_path('backend/assets/css/styles.css') @endphp

body {
   -webkit-print-color-adjust: exact; !important;
   background: #FFF;
   font-size: 14px;
   font-family: DejaVu Sans, sans-serif;
}

.classic-table{
	width:100%;
	color: #000;
}
.classic-table td{
	color: #000;
}

#invoice-item-table th{
	border: none;
}

#invoice-summary-table td{
	border: none !important;
}

#invoice-payment-history-table{
	margin-bottom: 50px;
}

#invoice-payment-history-table th{
	border: none !important;
}

#invoice-view{
   padding:15px 0px;
}

.invoice-note{
	margin-bottom: 50px;
}

.table th {
   background-color: #008ae2 !important;
   color: #FFF;
}

.table td {
   color: #2d2d2d;
}

.base_color{
	background-color: #008ae2 !important;
}

.invoice-col-6{
  width: 50%;
  float:left;
  padding-right: 0px;
  padding-left: 0px;
}

</style>
</head>

<body>

@php $base_currency = get_company_field( $invoice->company_id, 'base_currency', 'USD' ); @endphp
@php $date_format = get_company_field($invoice->company_id, 'date_format','Y-m-d'); @endphp
@php $currency = get_currency_symbol($base_currency); @endphp

@if($invoice->related_to == 'contacts' && isset($invoice->client))
	@php $client_currency = $invoice->client->currency; @endphp
	@php $client = $invoice->client; @endphp
@else
	@php $client_currency = $invoice->project->client->currency; @endphp
	@php $client = $invoice->project->client; @endphp
@endif

<div id="invoice-view" class="pdf">
	<div>
		<table class="classic-table">
			<tbody>
				<tr class="top">
					<td colspan="2">
						<table class="classic-table">
							<tbody>
								 <tr>
									<td class="wp-100">
										<img src="{{ get_pdf_company_logo($invoice->company_id) }}" class="wp-100">
									</td>
									<td>
										<div class="text-left">
											<b class="fs-22">{{ get_company_field($invoice->company_id,'company_name') }}</b><br>
											{{ get_company_field($invoice->company_id,'address') }}<br>
											{{ get_company_field($invoice->company_id,'email') }}<br>
											{!! get_company_field($invoice->company_id,'vat_id') != '' ? _lang('VAT ID').': '.clean(get_company_field($invoice->company_id,'vat_id')).'<br>' : '' !!}
											{!! get_company_field($invoice->company_id,'reg_no')!= '' ? _lang('REG NO').': '.clean(get_company_field($invoice->company_id,'reg_no')).'<br>' : '' !!}
										</div>
									</td>
									<td class="text-right">
										<img src="{{ public_path('images/modern-invoice-bg.jpg') }}" class="wp-300">
									</td>
								 </tr>
							</tbody>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2" class="pt-5">
						<div class="invoice-col-6 pt-3">
							 <h5><b>{{ _lang('Invoice To') }}</b></h5>
							 {{ $client->contact_name }}<br>
							 {{ $client->contact_email }}<br>
							 {!! $client->company_name != '' ? clean($client->company_name).'<br>' : '' !!}
							 {!! $client->address != '' ? clean($client->address).'<br>' : '' !!}
							 {!! $client->vat_id != '' ? _lang('VAT ID').': '.clean($client->vat_id).'<br>' : '' !!}
							 {!! $client->reg_no != '' ? _lang('REG NO').': '.clean($client->reg_no).'<br>' : '' !!}
						</div>

						<!--Company Address-->
						<div class="invoice-col-6 pt-3">
							<div class="d-inline-block float-md-right">
								<h5><b>{{ _lang('Invoice Details') }}</b></h5>

								<b>{{ _lang('Invoice') }} #:</b> {{ $invoice->invoice_number }}<br>

								<b>{{ _lang('Invoice Date') }}:</b> {{ date($date_format, strtotime( $invoice->invoice_date)) }}<br>

								<b>{{ _lang('Due Date') }}:</b> {{ date($date_format, strtotime( $invoice->due_date)) }}<br>

								<b>{{ _lang('Payment Status') }}:</b> {{ _dlang(str_replace('_',' ',$invoice->status)) }}<br>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	 </div>
	 <!--End Invoice Information-->
	 <div class="clearfix"></div>
	 <!--Invoice Product-->
	 <div>
		<table class="table table-bordered mt-2" id="invoice-item-table">
			 <thead class="base_color">
				 <tr>
					 <th>{{ _lang('Name') }}</th>
					 <th class="text-center wp-100">{{ _lang('Quantity') }}</th>
					 <th class="text-right">{{ _lang('Unit Cost') }}</th>
					 <th class="text-right wp-100">{{ _lang('Discount') }}</th>
					 <th>{{ _lang('Tax') }}</th>
					 <th class="text-right">{{ _lang('Line Total') }}</th>
				 </tr>
			 </thead>
			 <tbody id="invoice">
				 @foreach($invoice->invoice_items as $item)
					 <tr id="product-{{ $item->item_id }}">
						 <td>
							<b>{{ $item->item->item_name }}</b><br>{{ $item->description }}
						 </td>
						 <td class="text-center">{{ $item->quantity }}</td>
						 <td class="text-right">{!! strip_tags(decimalPlace($item->unit_cost, $currency)) !!}</td>
						 <td class="text-right">{!! strip_tags(decimalPlace($item->discount, $currency)) !!}</td>
						 <td>{!! clean(object_to_tax($item->taxes, 'name')) !!}</td>
						 <td class="text-right">{!! strip_tags(decimalPlace($item->sub_total, $currency)) !!}</td>
					 </tr>
				 @endforeach
			 </tbody>
		</table>
	 </div>
	 <!--End Invoice Product-->

	 <!--Summary Table-->
	 <div class="invoice-summary-right">
		<table class="table table-bordered" id="invoice-summary-table">
			<tbody>
				<tr>
					<td><b>{{ _lang('Sub Total') }}</b></td>
					<td class="text-right">
						<b>{!! strip_tags(decimalPlace($invoice->grand_total - $invoice->tax_total, $currency)) !!}</b>
					</td>
				</tr>
				@foreach($invoice_taxes as $tax)
				<tr>
					<td>{{ $tax->name }}</td>
					<td class="text-right">
						<span>{!! strip_tags(decimalPlace($tax->tax_amount, $currency)) !!}</span>
					</td>
				</tr>
				@endforeach
				<tr>
					 <td><b>{{ _lang('Grand Total') }}</b></td>
					 <td class="text-right">
						 <b>{!! strip_tags(decimalPlace($invoice->grand_total, $currency)) !!}</b>
						 @if($client_currency != $base_currency)
							<br><b>{!! strip_tags(decimalPlace($invoice->converted_total, get_currency_symbol($client_currency))) !!}</b>
						 @endif
					 </td>
				</tr>
				<tr>
					 <td>{{ _lang('Total Paid') }}</td>
					 <td class="text-right">
						<span>{!! strip_tags(decimalPlace($invoice->paid, $currency)) !!}</span>
						@if($client_currency != $base_currency)
							<br><span>{!! strip_tags(decimalPlace(convert_currency($base_currency, $client_currency, $invoice->paid), get_currency_symbol($client_currency))) !!}</span>
						@endif
					 </td>
				</tr>
				@if($invoice->status != 'Paid')
					<tr>
						 <td>{{ _lang('Amount Due') }}</td>
						 <td class="text-right">
							<span>{!! strip_tags(decimalPlace(($invoice->grand_total - $invoice->paid), $currency)) !!}</span>
							@if($client_currency != $base_currency)
							<br><span>{!! strip_tags(decimalPlace(convert_currency($base_currency, $client_currency, ($invoice->grand_total - $invoice->paid)), get_currency_symbol($client_currency))) !!}</span>
							@endif
						 </td>
					</tr>
				@endif
		    </tbody>
		</table>
	 </div>
	 <!--End Summary Table-->

	 <div class="clearfix"></div>

	 <!--Related Transaction-->
	 @if( ! $transactions->isEmpty() )
		<div>
			<table class="table table-bordered" id="invoice-payment-history-table">
				<thead class="base_color">
					<tr>
						<th>{{ _lang('Date') }}</th>
						<th>{{ _lang('Account') }}</th>
						<th class="text-right">{{ _lang('Amount') }}</th>
						<th class="text-right">{{ _lang('Base Amount') }}</th>
						<th>{{ _lang('Payment Method') }}</th>
					</tr>
				</thead>
				<tbody>
				   @foreach($transactions as $transaction)
						<tr id="transaction-{{ $transaction->id }}">
							<td>{{ date($date_format, strtotime($transaction->trans_date)) }}</td>
							<td>{{ $transaction->account->account_title }}</td>
							<td class="text-right">{!! strip_tags(decimalPlace($transaction->amount, get_currency_symbol($transaction->account->account_currency))) !!}</td>
							<td class="text-right">{!! strip_tags(decimalPlace($transaction->base_amount, $currency)) !!}</td>
							<td>{{ $transaction->payment_method->name }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	 @endif
	 <!--END Related Transaction-->

	 <!--Invoice Note-->
	 @if($invoice->note  != '')
		<div>
			<div class="invoice-note">{{ $invoice->note }}</div>
		</div>
	 @endif
	 <!--End Invoice Note-->

	 <!--Invoice Footer Text-->
	 @if(get_company_field($invoice->company_id,'invoice_footer') != '')
		<div>
			<div class="invoice-note">{!! xss_clean(get_company_field($invoice->company_id,'invoice_footer')) !!}</div>
		</div>
	 @endif
	 <!--End Invoice Note-->
</div>
</body>
</html>


