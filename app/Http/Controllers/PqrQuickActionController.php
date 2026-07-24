<?php
namespace App\Http\Controllers;
use App\Models\Pqr;
use App\Notifications\PqrEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class PqrQuickActionController extends Controller {
    public function update(Request $request,Pqr $pqr): RedirectResponse { $this->authorize('update',$pqr); $data=$request->validate(['estado'=>['nullable','in:radicada,en_revision,respondida,cerrada'],'assigned_to_id'=>['nullable','exists:users,id']]); $before=$pqr->only(['estado','assigned_to_id']); $pqr->update($data); $pqr->activities()->create(['user_id'=>$request->user()->id,'action'=>'quick_action','description'=>'Aplicó una acción rápida: estado '.$pqr->estado_label.', responsable '.($pqr->assignee?->name??'sin asignar').'.']); if(($before['estado']??null)!==$pqr->estado)$pqr->user?->notify(new PqrEventNotification($pqr,'Estado actualizado',"La solicitud ahora está {$pqr->estado_label}.")); return back()->with('success','Solicitud actualizada.'); }
}
