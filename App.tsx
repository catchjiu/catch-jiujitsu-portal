import React, { useState, useEffect, useCallback } from 'react';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { Schedule } from './pages/Schedule';
import { Payments } from './pages/Payments';
import { Admin } from './pages/Admin';
import type { User, ClassSession, Payment } from './types';
import * as api from './api';

const initialUser: User = {
  id: 0,
  name: 'Member',
  email: '',
  rank: 'White',
  stripes: 0,
  matHours: 0,
  isAdmin: false,
};

export default function App() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [user, setUser] = useState<User>(initialUser);
  const [classes, setClasses] = useState<ClassSession[]>([]);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [adminPayments, setAdminPayments] = useState<Payment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const nextClass = classes.find(c => c.isBookedByUser && new Date(c.startTime) > new Date());

  const loadUser = useCallback(async () => {
    try {
      const u = await api.getMe();
      setUser({
        id: u.id,
        name: u.name,
        email: u.email,
        rank: u.rank as User['rank'],
        stripes: u.stripes,
        matHours: u.matHours,
        isAdmin: u.isAdmin,
        avatarUrl: u.avatarUrl,
      });
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to load user');
    }
  }, []);

  const loadClasses = useCallback(async () => {
    try {
      const list = await api.getClassesUpcoming({ days: 14 });
      setClasses(list.map(c => ({
        ...c,
        type: c.type as ClassSession['type'],
      })));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to load classes');
    }
  }, []);

  const loadPayments = useCallback(async () => {
    try {
      const list = await api.getPayments();
      setPayments(list.map(p => ({
        ...p,
        status: p.status as Payment['status'],
      })));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to load payments');
    }
  }, []);

  const loadAdminPayments = useCallback(async () => {
    if (!user.isAdmin) return;
    try {
      const list = await api.getAdminPayments();
      setAdminPayments(list.map(p => ({
        ...p,
        status: p.status as Payment['status'],
      })));
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to load admin payments');
    }
  }, [user.isAdmin]);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      setError(null);
      try {
        await loadUser();
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [loadUser]);

  useEffect(() => {
    if (!user.id && !loading) return;
    loadClasses();
    loadPayments();
    loadAdminPayments();
  }, [user.id, user.isAdmin, loading, loadClasses, loadPayments, loadAdminPayments]);

  const handleBook = async (id: number) => {
    try {
      await api.bookClass(id);
      await loadClasses();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to book');
    }
  };

  const handleCancel = async (id: number) => {
    try {
      await api.cancelBooking(id);
      await loadClasses();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to cancel');
    }
  };

  const handlePaymentUpload = async (id: number, file: File) => {
    try {
      await api.uploadPaymentProof(id, file);
      await loadPayments();
      if (user.isAdmin) await loadAdminPayments();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Upload failed');
    }
  };

  const handleAdminApprove = async (id: number) => {
    try {
      await api.adminApprovePayment(id);
      await loadAdminPayments();
      await loadPayments();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to approve');
    }
  };

  const handleAdminReject = async (id: number) => {
    try {
      await api.adminRejectPayment(id);
      await loadAdminPayments();
      await loadPayments();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Failed to reject');
    }
  };

  const toggleAdmin = () => {
    setUser(prev => ({ ...prev, isAdmin: !prev.isAdmin }));
  };

  if (loading && !user.id) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950">
        <div className="text-slate-400">Loading…</div>
      </div>
    );
  }

  if (error && !user.id) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-slate-950">
        <div className="text-red-400 text-center">
          <p>{error}</p>
          <a href="/login" className="mt-4 inline-block text-brand-blue hover:underline">Go to Login</a>
        </div>
      </div>
    );
  }

  return (
    <Layout
      activeTab={activeTab}
      onTabChange={setActiveTab}
      user={user}
      toggleAdmin={toggleAdmin}
    >
      {error && (
        <div className="mb-4 p-3 rounded-lg bg-red-500/10 text-red-400 text-sm">
          {error}
          <button onClick={() => setError(null)} className="ml-2 underline">Dismiss</button>
        </div>
      )}
      {activeTab === 'dashboard' && <Dashboard user={user} nextClass={nextClass} />}
      {activeTab === 'schedule' && <Schedule classes={classes} onBook={handleBook} onCancel={handleCancel} />}
      {activeTab === 'payments' && <Payments payments={payments} onUpload={handlePaymentUpload} />}
      {activeTab === 'admin' && <Admin pendingPayments={adminPayments} onApprove={handleAdminApprove} onReject={handleAdminReject} />}
    </Layout>
  );
}
