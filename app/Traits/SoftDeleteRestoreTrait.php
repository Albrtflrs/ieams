<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Inertia\Inertia;

trait SoftDeleteRestoreTrait
{
    /**
     * Show trashed records.
     */
    public function trash()
    {
        $this->authorize('viewTrash', $this->getModelClass());

        $items = $this->getModelClass()::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(20);

        return Inertia::render($this->getTrashViewName(), [
            'items' => $items,
        ]);
    }

    /**
     * Restore a soft‑deleted record.
     */
    public function restore($id)
    {
        $item = $this->getModelClass()::withTrashed()->findOrFail($id);
        $this->authorize('restore', $item);
        $item->restore();
        return back()->with('success', 'Record restored.');
    }

    /**
     * Permanently delete a record.
     */
    public function forceDelete($id)
    {
        $item = $this->getModelClass()::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $item);
        $item->forceDelete();
        return back()->with('success', 'Record permanently deleted.');
    }

    /**
     * Get the model class for the controller.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the Inertia view name for the trash page.
     */
    abstract protected function getTrashViewName(): string;
}