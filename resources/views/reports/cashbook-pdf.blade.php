<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Monthly Cashbook · {{ $report['month_label'] }}</title>

    <style>
        @page {
            margin: 30px 34px 44px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #200e01;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            line-height: 1.45;
        }

        .masthead {
            width: 100%;
            border-bottom: 2px solid #5b0202;
            padding-bottom: 12px;
        }

        .masthead table {
            width: 100%;
            border-collapse: collapse;
        }

        .masthead td {
            vertical-align: bottom;
        }

        .brand-sub {
            margin-top: 3px;
            font-size: 7.5px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #8a7a68;
        }

        .report-title {
            margin: 22px 0 0;
            font-size: 25px;
            font-weight: bold;
            letter-spacing: -0.5px;
            color: #5b0202;
        }

        .report-month {
            margin-top: 5px;
            font-size: 11px;
            color: #6b5b4a;
        }

        .meta {
            margin-top: 4px;
            font-size: 8px;
            text-align: right;
            color: #8a7a68;
        }

        .section-label {
            margin: 22px 0 8px;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #8a7a68;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .summary-inner {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e1d8ae;
        }

        .summary-inner td {
            padding: 7px 10px;
            border-bottom: 1px solid #ede7c7;
        }

        .summary-inner tr:last-child td {
            border-bottom: 0;
        }

        .summary-label {
            color: #6b5b4a;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
            color: #200e01;
        }

        .summary-left {
            padding-right: 6px !important;
        }

        .summary-right {
            padding-left: 6px !important;
        }

        .combined {
            width: 100%;
            margin-top: 8px;
            border: 1px solid #5b0202;
            background: #5b0202;
            color: #ede7c7;
            padding: 11px 13px;
        }

        .combined table {
            width: 100%;
            border-collapse: collapse;
        }

        .combined td {
            vertical-align: middle;
        }

        .combined-label {
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #d9cfa9;
        }

        .combined-note {
            margin-top: 3px;
            font-size: 7.5px;
            color: #c6b98f;
        }

        .combined-value {
            text-align: right;
            font-size: 19px;
            font-weight: bold;
            white-space: nowrap;
        }

        .expenses {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .expenses thead th {
            background: #5b0202;
            color: #ede7c7;
            font-size: 7.5px;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: left;
            padding: 8px 9px;
        }

        .expenses tbody td {
            padding: 7px 9px;
            border-bottom: 1px solid #e1d8ae;
            vertical-align: top;
        }

        .expenses tbody tr:nth-child(even) td {
            background: #faf7ea;
        }

        .expenses .date {
            width: 17%;
            white-space: nowrap;
            color: #6b5b4a;
        }

        .expenses .description {
            width: 48%;
        }

        .expenses .paid-from {
            width: 15%;
        }

        .expenses .amount {
            width: 20%;
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
        }

        .expenses tfoot td {
            padding: 7px 9px;
            border-top: 1px solid #e1d8ae;
            color: #6b5b4a;
        }

        .expenses tfoot tr:first-child td {
            border-top: 1.5px solid #8b0000;
        }

        .expenses tfoot .amount {
            color: #200e01;
            font-weight: bold;
        }

        .expenses tfoot .grand-total td {
            background: #f8f4e3;
            border-top: 1.5px solid #5b0202;
            color: #200e01;
            font-size: 10px;
        }

        .empty {
            padding: 18px 10px;
            text-align: center;
            color: #8a7a68;
            font-style: italic;
            border-bottom: 1px solid #e1d8ae;
        }

        .note {
            margin-top: 16px;
            padding: 9px 11px;
            border-left: 3px solid #8b0000;
            background: #f8f4e3;
            color: #6b5b4a;
            font-size: 8px;
            line-height: 1.5;
        }

        .sign-off {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e1d8ae;
            font-size: 7.5px;
            color: #8a7a68;
        }

        .sign-off table {
            width: 100%;
            border-collapse: collapse;
        }

        .sign-off td:last-child {
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $summary = $report['summary'];
        $money = static fn (float|int $amount): string => 'BDT '.number_format((float) $amount, 2);
        $brandLogo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/rentra-logo.png')));
    @endphp

    <div class="masthead">
        <table>
            <tr>
                <td>
                    <img src="{{ $brandLogo }}" alt="Rentra" style="width: 80px; height: auto;">
                    <div class="brand-sub">Property accounts</div>
                </td>
                <td class="meta">
                    Generated {{ now()->format('d M Y, H:i') }}<br>
                    {{ $report['month_label'] }} · Cashbook
                </td>
            </tr>
        </table>
    </div>

    <h1 class="report-title">Monthly Cashbook</h1>
    <div class="report-month">{{ $report['month_label'] }} · Cash and bank summary</div>

    <div class="section-label">Financial summary</div>

    <table class="summary">
        <tr>
            <td class="summary-left">
                <table class="summary-inner">
                    <tr>
                        <td class="summary-label">Cash opening balance</td>
                        <td class="summary-value">{{ $money($summary['starting_cash']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Bank opening balance</td>
                        <td class="summary-value">{{ $money($summary['starting_bank']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Rent received in Cash</td>
                        <td class="summary-value">{{ $money($summary['cash_rent']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Rent received in Bank</td>
                        <td class="summary-value">{{ $money($summary['bank_rent']) }}</td>
                    </tr>
                </table>
            </td>
            <td class="summary-right">
                <table class="summary-inner">
                    <tr>
                        <td class="summary-label">Cash expenses total</td>
                        <td class="summary-value">{{ $money($summary['cash_expenses']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Bank expenses total</td>
                        <td class="summary-value">{{ $money($summary['bank_expenses']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Cash closing balance</td>
                        <td class="summary-value">{{ $money($summary['closing_cash']) }}</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Bank closing balance</td>
                        <td class="summary-value">{{ $money($summary['closing_bank']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="combined">
        <table>
            <tr>
                <td>
                    <div class="combined-label">Combined closing balance</div>
                    <div class="combined-note">Cash and bank together, after this month's movement.</div>
                </td>
                <td class="combined-value">{{ $money($summary['closing_total']) }}</td>
            </tr>
        </table>
    </div>

    <div class="section-label">Expense detail</div>

    <table class="expenses">
        <thead>
            <tr>
                <th class="date">Date</th>
                <th class="description">Description</th>
                <th class="paid-from">Paid from</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['expenses'] as $expense)
                <tr>
                    <td class="date">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="description">{{ $expense->description }}</td>
                    <td class="paid-from">{{ $expense->paid_from }}</td>
                    <td class="amount">{{ $money($expense->amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">No expenses recorded for this month.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Cash expenses subtotal</td>
                <td class="amount">{{ $money($summary['cash_expenses']) }}</td>
            </tr>
            <tr>
                <td colspan="3">Bank expenses subtotal</td>
                <td class="amount">{{ $money($summary['bank_expenses']) }}</td>
            </tr>
            <tr class="grand-total">
                <td colspan="3">Total expenses</td>
                <td class="amount">{{ $money($summary['expenses']) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="note">
        Opening balances are not rental income. Rent is recognised on its actual payment date;
        expenses are recognised on their expense date. Figures in this report match the Dashboard
        and Cashbook for {{ $report['month_label'] }}.
    </div>

    <div class="sign-off">
        <table>
            <tr>
                <td>Rentra · Monthly Cashbook · {{ $report['month_label'] }}</td>
                <td>Amounts in Bangladeshi Taka (BDT)</td>
            </tr>
        </table>
    </div>
</body>
</html>
