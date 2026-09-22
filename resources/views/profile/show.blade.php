@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'User Profile & Security')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">User Profile Settings</h1>
        <p class="text-xs text-slate-500">Manage your profile details, contact information, and password security.</p>
    </div>

    <!-- Profile Details Card -->
    <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
        @csrf
        @method('PUT')

        <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Personal Information</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="pt-3 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md shadow-emerald-900/20">
                Update Profile
            </button>
        </div>
    </form>

    <!-- Password Change Card -->
    <form action="{{ route('profile.password') }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
        @csrf
        @method('PUT')

        <h3 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100">Change Password</h3>

        <div class="space-y-3 max-w-md">
            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password *</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">New Password *</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password *</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="pt-3 flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold shadow-md">
                Update Password
            </button>
        </div>
    </form>
</div>
@endsection
