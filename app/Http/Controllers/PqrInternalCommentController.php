<?php
namespace App\Http\Controllers;
use App\Models\Pqr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class PqrInternalCommentController extends Controller {
    public function store(Request $request, Pqr $pqr): RedirectResponse { abort_unless($request->user()->canManagePqrs(),403); $data=$request->validate(['body'=>['required','string','max:3000']]); $pqr->internalComments()->create(['user_id'=>$request->user()->id,'body'=>$data['body']]); $pqr->activities()->create(['user_id'=>$request->user()->id,'action'=>'internal_comment','description'=>'Agregó un comentario interno.']); return back()->with('success','Comentario interno agregado.'); }
}
