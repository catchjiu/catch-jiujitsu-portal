import React, { useState } from 'react';
import { Payment, PaymentStatus } from '../types';
import { GlassCard } from '../components/ui/GlassCard';

interface PaymentsProps {
  payments: Payment[];
  onUpload: (paymentId: number, file: File) => void;
}

export const Payments: React.FC<PaymentsProps> = ({ payments, onUpload }) => {
  const [selectedMethod, setSelectedMethod] = useState<'Bank' | 'Line'>('Bank');
  const [activeUploadId, setActiveUploadId] = useState<number | null>(null);

  const getStatusColor = (status: PaymentStatus) => {
    switch(status) {
      case 'Paid': return 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20';
      case 'Pending Verification': return 'text-brand-gold bg-brand-gold/10 border-brand-gold/20';
      case 'Overdue': return 'text-red-400 bg-red-400/10 border-red-400/20';
      default: return 'text-slate-400';
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, paymentId: number) => {
    if (e.target.files && e.target.files[0]) {
      onUpload(paymentId, e.target.files[0]);
      setActiveUploadId(null); // Reset UI state
    }
  };

  // Find overdue or next payment
  const activePayment = payments.find(p => p.status === 'Overdue') || payments.find(p => p.status === 'Pending Verification') || payments[0];

  return (
    <div className="space-y-8">
      <div className="space-y-1">
        <h2 className="text-2xl font-display font-bold text-white uppercase tracking-wide">Payments</h2>
        <p className="text-slate-400 text-sm">Manage your monthly membership</p>
      </div>

      {/* Payment Instructions Card */}
      <GlassCard className="border-brand-blue/30 bg-slate-800/80">
        <h3 className="text-lg font-bold text-white mb-4">Payment Methods</h3>
        
        <div className="flex space-x-2 mb-6 p-1 bg-slate-900 rounded-lg">
          <button 
            onClick={() => setSelectedMethod('Bank')}
            className={`flex-1 py-2 text-sm font-bold rounded transition-colors ${selectedMethod === 'Bank' ? 'bg-slate-700 text-white' : 'text-slate-500'}`}
          >
            Bank Transfer
          </button>
          <button 
            onClick={() => setSelectedMethod('Line')}
            className={`flex-1 py-2 text-sm font-bold rounded transition-colors ${selectedMethod === 'Line' ? 'bg-slate-700 text-white' : 'text-slate-500'}`}
          >
            LinePay
          </button>
        </div>

        <div className="space-y-4">
          {selectedMethod === 'Bank' ? (
            <div className="text-center p-4 bg-slate-900 rounded border border-slate-700">
               <span className="material-symbols-outlined text-4xl text-slate-400 mb-2">account_balance</span>
               <p className="text-slate-400 text-xs uppercase tracking-widest mb-1">Kasikorn Bank</p>
               <p className="text-xl font-mono text-brand-gold select-all cursor-pointer">012-3-45678-9</p>
               <p className="text-slate-500 text-xs mt-1">Account Name: Catch Jiu Jitsu Co.</p>
            </div>
          ) : (
            <div className="text-center p-4 bg-slate-900 rounded border border-slate-700">
               <span className="material-symbols-outlined text-4xl text-green-500 mb-2">qr_code_2</span>
               <div className="w-32 h-32 bg-white mx-auto mb-2 rounded p-2 flex items-center justify-center">
                  {/* Mock QR */}
                  <div className="w-full h-full bg-slate-900 pattern-grid-lg opacity-20"></div> 
               </div>
               <p className="text-slate-500 text-xs">Scan via Line App</p>
            </div>
          )}
        </div>
      </GlassCard>

      <div className="space-y-4">
        <h3 className="text-lg font-bold text-white px-2">History & Status</h3>
        {payments.map(payment => (
          <GlassCard key={payment.id} className="flex flex-col gap-4">
            <div className="flex justify-between items-center">
              <div>
                <p className="text-sm text-slate-400 uppercase font-bold">{payment.month}</p>
                <p className="text-2xl font-display font-bold text-white">฿{payment.amount.toLocaleString()}</p>
              </div>
              <span className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border ${getStatusColor(payment.status)}`}>
                {payment.status}
              </span>
            </div>

            {payment.status !== 'Paid' && payment.status !== 'Pending Verification' && (
              <div className="mt-2 pt-4 border-t border-white/5">
                {activeUploadId === payment.id ? (
                  <div className="animate-fade-in">
                    <label className="block w-full cursor-pointer">
                      <input type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, payment.id)} />
                      <div className="w-full h-24 border-2 border-dashed border-brand-blue rounded-lg flex flex-col items-center justify-center bg-brand-blue/5 hover:bg-brand-blue/10 transition-colors">
                        <span className="material-symbols-outlined text-brand-blue">cloud_upload</span>
                        <span className="text-xs text-brand-blue mt-2 font-bold">Tap to Upload Slip</span>
                      </div>
                    </label>
                    <button 
                      onClick={() => setActiveUploadId(null)}
                      className="mt-2 w-full text-xs text-slate-500 py-2 hover:text-white"
                    >
                      Cancel
                    </button>
                  </div>
                ) : (
                  <button 
                    onClick={() => setActiveUploadId(payment.id)}
                    className="w-full py-3 bg-slate-100 text-slate-900 font-bold uppercase text-xs tracking-wider rounded hover:bg-white transition-colors flex items-center justify-center gap-2"
                  >
                    <span className="material-symbols-outlined text-sm">upload_file</span>
                    Submit Proof of Payment
                  </button>
                )}
              </div>
            )}
            {payment.status === 'Pending Verification' && (
               <p className="text-xs text-slate-500 text-center italic">Slip uploaded. Waiting for admin approval.</p>
            )}
          </GlassCard>
        ))}
      </div>
    </div>
  );
};