import React, { useRef, useEffect, useCallback } from 'react';

const VIEWPORT = { width: 1920, height: 1080 };

interface QRCheckInScreenProps {
  onScan: (code: string) => void;
}

export const QRCheckInScreen: React.FC<QRCheckInScreenProps> = ({ onScan }) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const bufferRef = useRef('');

  const submitCode = useCallback(() => {
    const fromInput = inputRef.current?.value?.trim() ?? '';
    const fromBuffer = bufferRef.current.trim();
    const code = fromInput || fromBuffer;
    bufferRef.current = '';
    if (inputRef.current) inputRef.current.value = '';
    if (code) onScan(code);
  }, [onScan]);

  useEffect(() => {
    const input = inputRef.current;
    if (!input) return;
    input.focus();

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        submitCode();
        return;
      }
      if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        bufferRef.current += e.key;
      }
    };

    const handlePaste = (e: ClipboardEvent) => {
      e.preventDefault();
      const text = (e.clipboardData?.getData('text') || '').trim();
      if (text) {
        bufferRef.current = text;
        submitCode();
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    input.addEventListener('paste', handlePaste);
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      input.removeEventListener('paste', handlePaste);
    };
  }, [submitCode]);

  return (
    <div
      className="bg-slate-950 text-slate-100 font-sans flex flex-col items-center justify-center overflow-hidden"
      style={{
        width: VIEWPORT.width,
        height: VIEWPORT.height,
        boxSizing: 'border-box',
      }}
    >
      {/* Invisible input for QR scanner (scanners often act as keyboard) */}
      <input
        ref={inputRef}
        type="text"
        autoComplete="off"
        autoFocus
        aria-label="QR code scan"
        className="absolute opacity-0 w-0 h-0 pointer-events-none"
        tabIndex={0}
      />

      {/* Logo */}
      <div className="flex items-center gap-3 mb-16">
        <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-brand-gold to-orange-600 flex items-center justify-center font-display font-bold text-black text-2xl">
          C
        </div>
        <h1 className="font-display font-bold text-4xl tracking-wider text-white">
          CATCH <span className="text-brand-gold">BJJ</span>
        </h1>
      </div>

      {/* Scan area card */}
      <div
        className="relative overflow-hidden rounded-3xl border-2 border-dashed border-slate-600 bg-slate-800/40 backdrop-blur-md border-white/10 p-16 flex flex-col items-center justify-center transition-colors focus-within:border-brand-gold focus-within:bg-slate-800/60"
        style={{ minWidth: 720, minHeight: 360 }}
      >
        <div className="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none" />
        <span className="material-symbols-outlined text-8xl text-slate-500 mb-6">qr_code_scanner</span>
        <h2 className="text-3xl font-display font-bold text-white uppercase tracking-wide mb-2">
          Scan to check in
        </h2>
        <p className="text-slate-400 text-xl">Position your QR code in front of the scanner</p>
        <p className="text-slate-500 text-sm mt-4">Or type your code and press Enter</p>
      </div>

      <p className="text-slate-500 text-sm mt-10">Ready for the next member</p>
    </div>
  );
};
