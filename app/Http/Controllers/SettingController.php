<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'pharmacy_name' => Setting::get('pharmacy_name', 'HealthCare Plus Pharmacy'),
            'pharmacy_address' => Setting::get('pharmacy_address', '14 Independence Avenue, Accra, Ghana'),
            'pharmacy_phone' => Setting::get('pharmacy_phone', '+233 24 123 4567'),
            'pharmacy_email' => Setting::get('pharmacy_email', 'info@healthcareplus.com'),
            'currency_symbol' => Setting::get('currency_symbol', 'GHS'),
            'currency_code' => Setting::get('currency_code', 'GHS'),
            'tax_percentage' => Setting::get('tax_percentage', 0),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV-'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', 20),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'pharmacy_name' => ['required', 'string', 'max:255'],
            'pharmacy_address' => ['nullable', 'string', 'max:255'],
            'pharmacy_phone' => ['nullable', 'string', 'max:50'],
            'pharmacy_email' => ['nullable', 'email', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_code' => ['required', 'string', 'max:10'],
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated as $key => $val) {
            $type = is_numeric($val) ? (is_float($val) ? 'float' : 'integer') : 'string';
            Setting::set($key, $val, 'general', $type);
        }

        AuditLog::log('update', 'Settings', null, "Updated pharmacy general settings");

        return back()->with('success', 'Pharmacy settings updated successfully.');
    }
}
