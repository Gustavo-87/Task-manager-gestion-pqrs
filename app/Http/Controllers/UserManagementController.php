<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class UserManagementController extends Controller {
    public function index(Request $request): View { abort_unless($request->user()->isAdmin(),403); return view('users.index',['users'=>User::orderBy('name')->paginate(20)]); }
    public function store(Request $request): RedirectResponse {
        abort_unless($request->user()->isAdmin(),403);
        $data=$request->validate([
            'name'=>['required','string','max:150'],
            'email'=>['required','email','max:150','unique:users,email'],
            'role'=>['required','in:admin,gestor,apoyo,auditor,residente'],
            'password'=>['required','string','min:8','confirmed'],
            'tower'=>['nullable','string','max:50'],
            'unit'=>['nullable','string','max:50'],
        ]);
        $data['email']=strtolower($data['email']);
        $data['email_verified_at']=now();
        User::create($data);
        return redirect()->route('users.index')->with('success','Usuario creado correctamente.');
    }
    public function edit(Request $request,User $user): View { abort_unless($request->user()->isAdmin(),403); return view('users.edit',compact('user')); }
    public function update(Request $request,User $user): RedirectResponse {
        abort_unless($request->user()->isAdmin(),403);
        $data=$request->validate([
            'name'=>['required','string','max:150'],
            'email'=>['required','email','max:150','unique:users,email,'.$user->id],
            'role'=>['required','in:admin,gestor,apoyo,auditor,residente'],
            'tower'=>['nullable','string','max:50'],
            'unit'=>['nullable','string','max:50'],
            'password'=>['nullable','string','min:8','confirmed'],
        ]);
        abort_if($user->is($request->user())&&$data['role']!=='admin',422,'No puedes retirar tu propio rol de administrador.');
        $data['email']=strtolower($data['email']);
        if(blank($data['password']??null)) unset($data['password']);
        $user->update($data);
        return redirect()->route('users.index')->with('success','Usuario actualizado correctamente.');
    }
    public function updateRole(Request $request,User $user): RedirectResponse { abort_unless($request->user()->isAdmin(),403); $data=$request->validate(['role'=>['required','in:admin,gestor,apoyo,auditor,residente']]); abort_if($user->is($request->user())&&$data['role']!=='admin',422,'No puedes retirar tu propio rol de administrador.'); $user->update($data); return back()->with('success','Rol actualizado.'); }
    public function destroy(Request $request,User $user): RedirectResponse {
        abort_unless($request->user()->isAdmin(),403);
        abort_if($user->is($request->user()),422,'No puedes eliminar tu propia cuenta.');
        abort_if($user->role==='admin'&&User::where('role','admin')->count()<=1,422,'Debe existir al menos un administrador.');
        abort_if($user->pqrs()->exists(),422,'No se puede eliminar porque tiene PQRS asociadas. Puedes cambiar su rol para conservar el historial.');
        $user->delete();
        return redirect()->route('users.index')->with('success','Usuario eliminado correctamente.');
    }
}
