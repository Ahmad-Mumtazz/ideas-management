<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdeaFileRequest;
use App\Models\Idea;
use App\Models\IdeaFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdeaFileController extends Controller
{
    /**
     * Store one or more documents against an idea the current user owns.
     *
     * Files go to the private `local` disk (storage/app/private), which has no
     * public URL, so the only way to reach them is the authorized route below.
     */
    public function store(IdeaFileRequest $request, Idea $idea): RedirectResponse
    {
        $this->authorize('update', $idea);

        $stored = 0;

        foreach ($request->file('files', []) as $upload) {
            // store() generates a random filename, so a hostile original name
            // can never influence the path on disk.
            $path = $upload->store('idea-files/'.$idea->user_id.'/'.$idea->id, 'local');

            $idea->files()->create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getClientMimeType(),
                'size' => $upload->getSize(),
            ]);

            $stored++;
        }

        $idea->recordActivity('file', $stored.' '.str('file')->plural($stored).' uploaded');

        return back()->with('success', $stored.' '.str('file')->plural($stored).' uploaded.');
    }

    /**
     * Stream a stored file to its owner as a download.
     */
    public function show(IdeaFile $file): StreamedResponse
    {
        $this->authorize('view', $file);

        $disk = Storage::disk($file->disk ?: 'local');

        abort_if(! $disk->exists($file->path), 404, 'That file is no longer available.');

        // Always sent as an attachment — nothing is rendered inline, so an
        // uploaded SVG or HTML-ish document cannot execute on this origin.
        return $disk->download($file->path, $file->original_name);
    }

    public function destroy(IdeaFile $file): RedirectResponse
    {
        $this->authorize('delete', $file);

        $name = $file->original_name;
        $idea = $file->idea;

        // The model's `deleting` hook removes the file from storage.
        $file->delete();

        $idea->recordActivity('file', 'Deleted file: '.$name);

        return back()->with('success', '"'.$name.'" was deleted.');
    }
}
