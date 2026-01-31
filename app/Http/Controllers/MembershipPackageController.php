<?php

namespace App\Http\Controllers;

use App\Models\MembershipPackage;
use Illuminate\Http\Request;

class MembershipPackageController extends Controller
{
    /**
     * Display a listing of membership packages.
     */
    public function index()
    {
        $packages = MembershipPackage::ordered()->get();

        return view('admin.membership-packages', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('admin.membership-package-create');
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_type' => 'required|in:days,weeks,months,years,classes',
            'duration_value' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'age_group' => 'required|in:Adults,Kids,All',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = MembershipPackage::max('sort_order') + 1;

        MembershipPackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    /**
     * Show the form for editing a package.
     */
    public function edit($id)
    {
        $package = MembershipPackage::findOrFail($id);

        return view('admin.membership-package-edit', [
            'package' => $package,
        ]);
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, $id)
    {
        $package = MembershipPackage::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_type' => 'required|in:days,weeks,months,years,classes',
            'duration_value' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'age_group' => 'required|in:Adults,Kids,All',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Remove the specified package.
     */
    public function destroy($id)
    {
        $package = MembershipPackage::findOrFail($id);
        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }

    /**
     * Toggle package active status.
     */
    public function toggleStatus($id)
    {
        $package = MembershipPackage::findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);

        return redirect()->route('admin.packages.index')->with('success', 'Package status updated.');
    }
}
