<?php
namespace App\Http\Controllers;
use App\Models\Pqr;
use App\Models\PqrReply;
use App\Notifications\PqrEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
class PqrReplyController extends Controller {
    public function store(Request $request, Pqr $pqr): RedirectResponse
    {
        abort_unless($request->user()->canManagePqrs(), 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:10000'], 'action' => ['required', 'in:draft,send'], 'attachments' => ['nullable', 'array', 'max:5'], 'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv,zip']]);
        $files = collect($request->file('attachments', []))->map(function ($file) use ($pqr) { return ['name' => $file->getClientOriginalName(), 'path' => $file->store("pqrs/{$pqr->id}/replies"), 'size' => $file->getSize()]; })->all();
        $draft = $data['action'] === 'draft';
        $reply = $pqr->replies()->create(['user_id' => $request->user()->id, 'body' => $data['body'], 'is_draft' => $draft, 'attachments' => $files, 'sent_at' => $draft ? null : now()]);
        $pqr->activities()->create(['user_id' => $request->user()->id, 'action' => $draft ? 'drafted_reply' : 'sent_reply', 'description' => $draft ? 'Guardó un borrador de respuesta.' : 'Envió una respuesta al residente.']);
        if (! $draft) { $pqr->update(['estado' => 'respondida']); $pqr->user?->notify(new PqrEventNotification($pqr, 'Tu solicitud fue respondida', "La solicitud PQR-".str_pad($pqr->id, 4, '0', STR_PAD_LEFT).' tiene una nueva respuesta.')); }
        return back()->with('success', $draft ? 'Borrador guardado.' : 'Respuesta enviada y residente notificado.');
    }
    public function download(Request $request, Pqr $pqr, PqrReply $reply, int $file): StreamedResponse
    {
        $this->authorize('view', $pqr); abort_unless($reply->pqr_id === $pqr->id && isset($reply->attachments[$file]), 404); $attachment = $reply->attachments[$file];
        return Storage::disk('local')->download($attachment['path'], $attachment['name']);
    }
}
