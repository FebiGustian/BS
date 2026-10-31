<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    private array $types = [
        'asset' => 'Aset',
        'liability' => 'Liabilitas',
        'equity' => 'Ekuitas',
        'revenue' => 'Pendapatan',
        'expense' => 'Beban',
    ];

    public function index(Request $request)
    {
        $query = Account::with('parent')->orderBy('code');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $accounts = $query->paginate(15)->withQueryString();

        $totalAccounts = Account::count();
        $activeAccounts = Account::where('is_active', true)->count();

        return view('accounts.index', [
            'accounts' => $accounts,
            'types' => $this->types,
            'totalAccounts' => $totalAccounts,
            'activeAccounts' => $activeAccounts,
        ]);
    }

    public function create()
    {
        $types = $this->types;

        $parents = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounts.create', compact('types', 'parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(array_keys($this->types))],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->validasiParent(
            $data['parent_id'] ?? null,
            $data['type']
        );

        $data['is_active'] = $request->boolean('is_active');
        $data['is_system'] = false;

        Account::create($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        $types = $this->types;

        $parents = Account::where('is_active', true)
            ->where('id', '!=', $account->id)
            ->where('type', $account->type)
            ->orderBy('code')
            ->get();

        return view('accounts.edit', compact('account', 'types', 'parents'));
    }

    public function update(Request $request, Account $account)
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (! $account->is_system) {
            $rules['code'] = [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts', 'code')->ignore($account->id),
            ];

            $rules['type'] = [
                'required',
                Rule::in(array_keys($this->types)),
            ];

            $rules['normal_balance'] = [
                'required',
                Rule::in(['debit', 'credit']),
            ];

            $rules['parent_id'] = [
                'nullable',
                'exists:accounts,id',
                Rule::notIn([$account->id]),
            ];
        }

        $data = $request->validate($rules);

        if (! $account->is_system) {
            $this->validasiParent(
                $data['parent_id'] ?? null,
                $data['type'],
                $account->id
            );
        }

        $data['is_active'] = $request->boolean('is_active');

        $account->update($data);

        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    private function validasiParent(
        ?int $parentId,
        string $type,
        ?int $accountId = null
    ): void {
        if (! $parentId) {
            return;
        }

        $parent = Account::with('parent')->findOrFail($parentId);

        if ($parent->type !== $type) {
            throw ValidationException::withMessages([
                'parent_id' => 'Jenis akun induk harus sama dengan jenis akun anak.',
            ]);
        }

        $current = $parent;

        while ($current) {
            if ($accountId && $current->id === $accountId) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Relasi akun induk tidak boleh membentuk siklus.',
                ]);
            }

            $current = $current->parent;
        }
    }
}
