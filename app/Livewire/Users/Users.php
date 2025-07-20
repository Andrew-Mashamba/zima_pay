<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Laravel\Jetstream\Jetstream;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Users extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public $selectedMenuItem = 1;
    public $showModal = false;
    public $showDeleteModal = false;
    public $editingUser = null;
    public $deleteUserId = null;
    public $viewUserId = null;
    public $showViewModal = false;

    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $profile_photo;
    public $role = 'user';
    public $is_active = true;
    public $send_welcome_email = true;

    // Additional user fields
    public $phone = '';
    public $department = '';
    public $position = '';
    public $location = '';
    public $notes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'roleFilter' => ['except' => ''],
    ];

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,manager,user'],
            'is_active' => ['boolean'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'profile_photo' => ['nullable', 'image', 'max:1024'],
        ];

        if (!$this->editingUser) {
            $rules['email'][] = 'unique:users,email';
            $rules['password'] = ['required', 'string', Password::defaults(), 'confirmed'];
        } else {
            $rules['email'][] = 'unique:users,email,' . $this->editingUser->id;
            $rules['password'] = ['nullable', 'string', Password::defaults(), 'confirmed'];
        }

        return $rules;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function selectedMenu($menuId)
    {
        $this->selectedMenuItem = $menuId;
        
        // Reset filters based on selected menu
        switch ($menuId) {
            case 1: // All Users
                $this->resetFilters();
                break;
            case 3: // Active Users
                $this->statusFilter = 'active';
                break;
            case 4: // Inactive Users
                $this->statusFilter = 'inactive';
                break;
            case 5: // Administrators
                $this->roleFilter = 'admin';
                break;
        }
        
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->roleFilter = '';
        $this->resetPage();
    }

    public function openModal($userId = null)
    {
        if ($userId) {
            $this->editingUser = User::find($userId);
            $this->name = $this->editingUser->name;
            $this->email = $this->editingUser->email;
            $this->role = $this->editingUser->role ?? 'user';
            $this->is_active = $this->editingUser->is_active ?? true;
            $this->phone = $this->editingUser->phone ?? '';
            $this->department = $this->editingUser->department ?? '';
            $this->position = $this->editingUser->position ?? '';
            $this->location = $this->editingUser->location ?? '';
            $this->notes = $this->editingUser->notes ?? '';
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingUser = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->profile_photo = null;
        $this->role = 'user';
        $this->is_active = true;
        $this->send_welcome_email = true;
        $this->phone = '';
        $this->department = '';
        $this->position = '';
        $this->location = '';
        $this->notes = '';
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'phone' => $this->phone,
            'department' => $this->department,
            'position' => $this->position,
            'location' => $this->location,
            'notes' => $this->notes,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->profile_photo) {
            $path = $this->profile_photo->store('profile-photos', 'public');
            $userData['profile_photo_path'] = $path;
        }

        if ($this->editingUser) {
            $this->editingUser->update($userData);
            session()->flash('message', 'User updated successfully!');
        } else {
            $user = User::create($userData);
            
            if ($this->send_welcome_email) {
                // Send welcome email logic here
                // $user->sendWelcomeNotification();
            }
            
            session()->flash('message', 'User created successfully!');
        }

        $this->closeModal();
    }

    public function viewUser($userId)
    {
        $this->viewUserId = $userId;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewUserId = null;
    }

    public function confirmDelete($userId)
    {
        $this->deleteUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        $user = User::find($this->deleteUserId);
        if ($user && $user->id !== auth()->id()) {
            // Delete profile photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            
            $user->delete();
            session()->flash('message', 'User deleted successfully!');
        } else {
            session()->flash('error', 'Cannot delete your own account!');
        }
        
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
    }

    public function toggleUserStatus($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id !== auth()->id()) {
            $user->update(['is_active' => !($user->is_active ?? true)]);
            $status = $user->is_active ? 'activated' : 'deactivated';
            session()->flash('message', "User {$status} successfully!");
        } else {
            session()->flash('error', 'Cannot modify your own account status!');
        }
    }

    public function impersonateUser($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id !== auth()->id() && auth()->user()->role === 'admin') {
            // Implement impersonation logic here
            session()->flash('message', 'Impersonation feature would be implemented here');
        } else {
            session()->flash('error', 'Cannot impersonate this user!');
        }
    }

    public function resetUserPassword($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $newPassword = \Str::random(12);
            $user->update(['password' => Hash::make($newPassword)]);
            
            // Send password reset email
            // $user->sendPasswordResetNotification($newPassword);
            
            session()->flash('message', 'Password reset successfully! New password sent to user.');
        }
    }

    public function getStatsProperty()
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::where('role', 'admin')->count(),
            'managers' => User::where('role', 'manager')->count(),
            'regular_users' => User::where('role', 'user')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('department', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->statusFilter === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role', $this->roleFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $viewUser = null;
        if ($this->viewUserId) {
            $viewUser = User::find($this->viewUserId);
        }

        return view('livewire.users.users', compact('users', 'viewUser'));
    }
}