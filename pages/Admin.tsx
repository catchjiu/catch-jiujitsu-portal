import React from 'react';
import { Payment } from '../types';
import { GlassCard } from '../components/ui/GlassCard';

interface AdminProps {
  pendingPayments: Payment[];
  onApprove: (id: number) => void;
  onReject: (id: number) => void;
}

export const Admin: React.FC<AdminProps> = ({ pendingPayments, onApprove, onReject }) => {
  return (
    <div className="space-y-6">
      <div className="space-y-1">
        <h2 className="text-2xl font-display font-bold text-white uppercase tracking-wide">Admin Dashboard</h2>
        <p className="text-slate-400 text-sm">Verify Member Payments</p>
      </div>

      <a
        href="/checkin.html"
        target="_blank"
        rel="noopener noreferrer"
        className="block"
      >
        <GlassCard className="border-t-4 border-t-brand-gold flex items-center gap-4 hover:bg-slate-800/60 transition-colors">
          <div className="w-12 h-12 rounded-xl bg-brand-gold/20 flex items-center justify-center flex-shrink-0">
            <span className="material-symbols-outlined text-2xl text-brand-gold">qr_code_scanner</span>
          </div>
          <div className="flex-1 min-w-0">
            <h3 className="font-display font-bold text-white uppercase tracking-wide">Check-In Kiosk</h3>
            <p className="text-slate-400 text-sm">Open QR check-in screen for monitor (1920×1080)</p>
          </div>
          <span className="material-symbols-outlined text-slate-500 flex-shrink-0">open_in_new</span>
        </GlassCard>
      </a>

      {pendingPayments.length === 0 ? (
        <div className="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
          <span className="material-symbols-outlined text-4xl mb-2">check_circle</span>
          <p>No pending payments.</p>
        </div>
      ) : (
        <div className="space-y-4">
          {pendingPayments.map(payment => (
            <GlassCard key={payment.id} className="border-l-4 border-l-brand-gold">
              <div className="flex justify-between items-start mb-4">
                <div>
                   <p className="text-xs text-slate-400 uppercase font-bold mb-1">Submission ID: #{payment.id}</p>
                   <p className="text-white font-bold">{payment.month}</p>
                   <p className="text-xl text-brand-gold font-mono">฿{payment.amount.toLocaleString()}</p>
                </div>
                <div className="text-right text-xs text-slate-500">
                  {new Date(payment.submittedAt!).toLocaleDateString()}
                </div>
              </div>

              {/* Proof Image Preview */}
              <div className="mb-4 bg-black rounded-lg overflow-hidden h-48 w-full relative group">
                <img src={payment.proofImageUrl} alt="Proof" className="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" />
                <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40">
                  <span className="text-white text-xs font-bold uppercase border border-white px-3 py-1 rounded">View Full</span>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <button 
                  onClick={() => onReject(payment.id)}
                  className="py-3 rounded bg-red-500/10 text-red-500 font-bold uppercase text-xs hover:bg-red-500/20 transition-colors border border-red-500/20"
                >
                  Reject
                </button>
                <button 
                  onClick={() => onApprove(payment.id)}
                  className="py-3 rounded bg-emerald-500 text-white font-bold uppercase text-xs hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20"
                >
                  Approve
                </button>
              </div>
            </GlassCard>
          ))}
        </div>
      )}
    </div>
  );
};