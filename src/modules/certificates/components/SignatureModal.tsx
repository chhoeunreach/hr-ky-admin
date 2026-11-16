import React, { useRef, useState, useEffect } from 'react';
import { X, Check, Trash2, Upload, PenTool, Type } from 'lucide-react';
import { Signatory, SignatureStyleOption } from '../types/certificate';

interface SignatureModalProps {
  isOpen: boolean;
  onClose: () => void;
  signatory: Signatory | null;
  signatoryIndex: number;
  onSaveSignatory: (index: number, updated: Signatory) => void;
}

export const SignatureModal: React.FC<SignatureModalProps> = ({
  isOpen,
  onClose,
  signatory,
  signatoryIndex,
  onSaveSignatory,
}) => {
  if (!isOpen || !signatory) return null;

  const [mode, setMode] = useState<'font' | 'draw' | 'upload'>(
    signatory.customSignatureData ? 'draw' : 'font'
  );
  const [name, setName] = useState(signatory.name);
  const [role, setRole] = useState(signatory.role);
  const [organization, setOrganization] = useState(signatory.organization || '');
  const [signatureStyle, setSignatureStyle] = useState<SignatureStyleOption>(
    signatory.signatureStyle || 'alex-brush'
  );
  const [customDataUrl, setCustomDataUrl] = useState<string | undefined>(
    signatory.customSignatureData
  );

  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const isDrawing = useRef(false);

  useEffect(() => {
    if (mode === 'draw' && canvasRef.current) {
      const canvas = canvasRef.current;
      const ctx = canvas.getContext('2d');
      if (ctx) {
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 2.5;
        if (customDataUrl) {
          const img = new Image();
          img.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          };
          img.src = customDataUrl;
        }
      }
    }
  }, [mode]);

  const startDrawing = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    isDrawing.current = true;
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const rect = canvas.getBoundingClientRect();
    const x = 'touches' in e ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = 'touches' in e ? e.touches[0].clientY - rect.top : e.clientY - rect.top;
    ctx.beginPath();
    ctx.moveTo(x, y);
  };

  const draw = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    if (!isDrawing.current) return;
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    const rect = canvas.getBoundingClientRect();
    const x = 'touches' in e ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = 'touches' in e ? e.touches[0].clientY - rect.top : e.clientY - rect.top;
    ctx.lineTo(x, y);
    ctx.stroke();
  };

  const stopDrawing = () => {
    if (!isDrawing.current) return;
    isDrawing.current = false;
    if (canvasRef.current) {
      setCustomDataUrl(canvasRef.current.toDataURL('image/png'));
    }
  };

  const clearCanvas = () => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    setCustomDataUrl(undefined);
  };

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (uploadEvent) => {
        const result = uploadEvent.target?.result as string;
        setCustomDataUrl(result);
        setMode('upload');
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSave = () => {
    const updated: Signatory = {
      ...signatory,
      name,
      role,
      organization,
      signatureStyle,
      customSignatureData: (mode === 'draw' || mode === 'upload') ? customDataUrl : undefined,
    };
    onSaveSignatory(signatoryIndex, updated);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-lg bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">
              Customize Signatory & Signature
            </h3>
            <p className="text-xs text-slate-500 dark:text-slate-400">
              Set name, executive title, and authentic signature rendering
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
          {/* Details */}
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Signatory Name
              </label>
              <input
                type="text"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Role / Title
              </label>
              <input
                type="text"
                value={role}
                onChange={(e) => setRole(e.target.value)}
                className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Organization / Department (Optional)
            </label>
            <input
              type="text"
              value={organization}
              onChange={(e) => setOrganization(e.target.value)}
              className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100"
            />
          </div>

          {/* Mode Switcher */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
              Signature Method
            </label>
            <div className="grid grid-cols-3 gap-2">
              <button
                type="button"
                onClick={() => setMode('font')}
                className={`flex items-center justify-center gap-1.5 py-2 text-xs font-medium rounded-lg border transition-all cursor-pointer ${
                  mode === 'font'
                    ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50'
                }`}
              >
                <Type className="w-3.5 h-3.5" />
                <span>Calligraphy Script</span>
              </button>
              <button
                type="button"
                onClick={() => setMode('draw')}
                className={`flex items-center justify-center gap-1.5 py-2 text-xs font-medium rounded-lg border transition-all cursor-pointer ${
                  mode === 'draw'
                    ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50'
                }`}
              >
                <PenTool className="w-3.5 h-3.5" />
                <span>Draw on Canvas</span>
              </button>
              <button
                type="button"
                onClick={() => setMode('upload')}
                className={`flex items-center justify-center gap-1.5 py-2 text-xs font-medium rounded-lg border transition-all cursor-pointer ${
                  mode === 'upload'
                    ? 'border-amber-500 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50'
                }`}
              >
                <Upload className="w-3.5 h-3.5" />
                <span>Upload PNG</span>
              </button>
            </div>
          </div>

          {/* Mode-specific content */}
          {mode === 'font' && (
            <div className="space-y-2">
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Choose Script Style
              </label>
              <div className="grid grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => setSignatureStyle('alex-brush')}
                  className={`p-3 rounded-lg border text-left cursor-pointer transition-all ${
                    signatureStyle === 'alex-brush'
                      ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20'
                      : 'border-slate-200 dark:border-slate-800'
                  }`}
                >
                  <div className="text-xl font-signature text-slate-800 dark:text-slate-200">
                    {name || 'Alex Brush Script'}
                  </div>
                  <div className="text-[10px] text-slate-500 mt-1">Cursive Signature</div>
                </button>
                <button
                  type="button"
                  onClick={() => setSignatureStyle('great-vibes')}
                  className={`p-3 rounded-lg border text-left cursor-pointer transition-all ${
                    signatureStyle === 'great-vibes'
                      ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20'
                      : 'border-slate-200 dark:border-slate-800'
                  }`}
                >
                  <div className="text-xl font-script text-slate-800 dark:text-slate-200">
                    {name || 'Great Vibes Flow'}
                  </div>
                  <div className="text-[10px] text-slate-500 mt-1">Formal Flourished Script</div>
                </button>
              </div>
            </div>
          )}

          {mode === 'draw' && (
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <label className="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                  Draw Signature Here
                </label>
                <button
                  type="button"
                  onClick={clearCanvas}
                  className="flex items-center gap-1 text-[11px] text-red-600 hover:text-red-700 font-medium cursor-pointer"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                  <span>Clear Pad</span>
                </button>
              </div>
              <div className="border border-slate-300 dark:border-slate-700 rounded-lg overflow-hidden bg-white touch-none">
                <canvas
                  ref={canvasRef}
                  width={460}
                  height={140}
                  onMouseDown={startDrawing}
                  onMouseMove={draw}
                  onMouseUp={stopDrawing}
                  onMouseLeave={stopDrawing}
                  onTouchStart={startDrawing}
                  onTouchMove={draw}
                  onTouchEnd={stopDrawing}
                  className="w-full h-36 cursor-crosshair"
                />
              </div>
              <p className="text-[11px] text-slate-500">
                Sign with your mouse, trackpad, or finger.
              </p>
            </div>
          )}

          {mode === 'upload' && (
            <div className="space-y-3">
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Upload Transparent Signature (PNG/JPEG)
              </label>
              <input
                type="file"
                accept="image/*"
                onChange={handleFileUpload}
                className="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 dark:file:bg-slate-800 file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200"
              />
              {customDataUrl && (
                <div className="p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg flex items-center justify-center">
                  <img
                    src={customDataUrl}
                    alt="Uploaded Signature"
                    className="max-h-16 w-auto object-contain"
                  />
                </div>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg"
          >
            Cancel
          </button>
          <button
            type="button"
            onClick={handleSave}
            className="flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 rounded-lg hover:bg-slate-800 dark:hover:bg-slate-200 cursor-pointer"
          >
            <Check className="w-3.5 h-3.5" />
            <span>Apply Signatory</span>
          </button>
        </div>
      </div>
    </div>
  );
};
