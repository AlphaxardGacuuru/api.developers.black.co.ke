<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .receipt-container {
            padding: 40px;
            background: #fff;
        }

        /* Tables for Layout */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        td {
            vertical-align: top;
        }

        /* Header Area */
        .header-table td {
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }
        .receipt-title {
            font-size: 24px;
            text-align: right;
            margin: 0;
            color: #111827;
            font-weight: bold;
        }

        /* Payment Info Section */
        .info-table {
            margin-top: 30px;
        }
        .info-label {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        .info-text {
            color: #4b5563;
        }

        /* Details Table */
        .details-table {
            margin-top: 20px;
        }
        .details-table th {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid #374151;
            color: #374151;
            font-size: 12px;
        }
        .details-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right { text-align: right; }

        /* Total Row */
        .total-row {
            border-top: 2px solid #111827;
            font-weight: bold;
            font-size: 16px;
        }
        .total-row td {
            padding-top: 15px;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        /* Footer */
        .footer {
            margin-top: 60px;
            text-align: center;
        }
        .thanks {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .business-info {
            text-align: right;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">
						<img src="{{ public_path('images/default-monochrome-black.svg') }}" alt="Logo" style="height: 40px;">
					</div>
                </td>
                <td class="text-right">
                    <h2 class="receipt-title">PAYMENT RECEIPT</h2>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="info-label">Payment From</div>
                    <div class="info-text">
                        Client: {{ $payment->user->name }}<br>
                        Email: {{ $payment->user->email }}<br>
                        @if($payment->user->phone)
                            Phone: {{ $payment->user->phone }}
                        @endif
                    </div>
                </td>
                <td width="50%" class="text-right">
                    <div class="info-label">Payment Details</div>
                    <div class="info-text">
                        Payment Date: {{ $payment->payment_date->format('d M Y') }}<br>
                    </div>
                </td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Payment for Invoice: {{ $payment->invoice->number }}</td>
                    <td class="text-right">
                        <small>KES</small> {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
                <tr class="total-row">
                    <td class="text-right">Total Paid:</td>
                    <td class="text-right">
                        <small>KES</small> {{ number_format($payment->amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        @if($payment->notes)
        <div class="notes-section">
            <div class="info-label">Notes</div>
            <div class="info-text" style="font-size: 11px;">
                {{ $payment->notes }}
            </div>
        </div>
        @endif

        <div class="footer">
            <div class="thanks">Thank you for your payment!</div>
        </div>

        <div class="business-info">
            <div class="info-label">Black Developers</div>
            <div class="info-text">
                Email: al@developers.black.co.ke<br>
                Phone: +254 700 364446
            </div>
        </div>
    </div>
</body>
</html>