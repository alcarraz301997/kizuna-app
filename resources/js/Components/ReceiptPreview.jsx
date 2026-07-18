import { useForm } from '@inertiajs/react';

export default function ReceiptPreview({ receipts = [] }) {
    const { delete: destroy, processing } = useForm({});

    const isImage = (mimeType) => {
        return mimeType && mimeType.startsWith('image/');
    };

    const formatSize = (bytes) => {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    };

    const handleDelete = (receiptId) => {
        if (confirm('¿Eliminar este adjunto? Esta acción no se puede deshacer.')) {
            destroy(route('receipts.destroy', receiptId), {
                preserveScroll: true,
            });
        }
    };

    if (receipts.length === 0) {
        return (
            <p className="text-sm text-gray-500">No hay adjuntos todavía.</p>
        );
    }

    return (
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {receipts.map((receipt) => (
                <div
                    key={receipt.id}
                    className="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3"
                >
                    {isImage(receipt.file_type) ? (
                        <div className="h-16 w-16 shrink-0 overflow-hidden rounded border border-gray-200">
                            <img
                                src={receipt.file_url}
                                alt={receipt.file_name}
                                className="h-full w-full object-cover"
                            />
                        </div>
                    ) : (
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded border border-gray-200 bg-gray-50">
                            <svg
                                className="h-8 w-8 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    )}
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium text-gray-900">
                            {receipt.file_name}
                        </p>
                        <p className="text-xs text-gray-500">
                            {formatSize(receipt.file_size)}
                        </p>
                        <div className="mt-2 flex gap-2">
                            <a
                                href={receipt.file_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-xs text-indigo-600 hover:text-indigo-900"
                            >
                                Ver
                            </a>
                            <button
                                type="button"
                                onClick={() => handleDelete(receipt.id)}
                                disabled={processing}
                                className="text-xs text-red-600 hover:text-red-900 disabled:opacity-50"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
