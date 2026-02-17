import React, { useState } from 'react';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { Schedule } from './pages/Schedule';
import { Payments } from './pages/Payments';
import { Admin } from './pages/Admin';
import { mockClasses, mockPayments, mockAdminPayments, currentUser } from './mockData';
import { Payment, PaymentStatus } from './types';

export default function App() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [user, setUser] = useState(currentUser);
  
  // State for Booking Logic (Simulation)
  const [classes, setClasses] = useState(mockClasses);
  const nextClass = classes.find(c => c.isBookedByUser && new Date(c.startTime) > new Date());

  // State for Payment Logic (Simulation)
  const [payments, setPayments] = useState(mockPayments);
  const [adminPayments, setAdminPayments] = useState(mockAdminPayments);

  const handleBook = (id: number) => {
    setClasses(prev => prev.map(c => c.id === id ? { ...c, bookedCount: c.bookedCount + 1, isBookedByUser: true } : c));
  };

  const handleCancel = (id: number) => {
    setClasses(prev => prev.map(c => c.id === id ? { ...c, bookedCount: c.bookedCount - 1, isBookedByUser: false } : c));
  };

  const handlePaymentUpload = (id: number, file: File) => {
    // In a real app, upload file to S3/Storage, get URL.
    // Here we simulate moving it to "Pending Verification"
    setPayments(prev => prev.map(p => p.id === id ? { ...p, status: 'Pending Verification' as PaymentStatus } : p));
    
    // Add to admin queue (Simulation)
    const payment = payments.find(p => p.id === id);
    if(payment) {
        setAdminPayments(prev => [...prev, { ...payment, status: 'Pending Verification', proofImageUrl: "https://picsum.photos/400/600", submittedAt: new Date().toISOString() }]);
    }
  };

  const handleAdminApprove = (id: number) => {
    setAdminPayments(prev => prev.filter(p => p.id !== id));
    // Update user view if applicable (simulation logic limitation: ids match across mock arrays)
    setPayments(prev => prev.map(p => p.id === id ? { ...p, status: 'Paid' as PaymentStatus } : p));
  };

  const handleAdminReject = (id: number) => {
    setAdminPayments(prev => prev.filter(p => p.id !== id));
    setPayments(prev => prev.map(p => p.id === id ? { ...p, status: 'Rejected' as PaymentStatus } : p));
  };

  const toggleAdmin = () => {
    setUser(prev => ({ ...prev, isAdmin: !prev.isAdmin }));
  };

  return (
    <Layout 
      activeTab={activeTab} 
      onTabChange={setActiveTab} 
      user={user}
      toggleAdmin={toggleAdmin}
    >
      {activeTab === 'dashboard' && <Dashboard user={user} nextClass={nextClass} />}
      {activeTab === 'schedule' && <Schedule classes={classes} onBook={handleBook} onCancel={handleCancel} />}
      {activeTab === 'payments' && <Payments payments={payments} onUpload={handlePaymentUpload} />}
      {activeTab === 'admin' && <Admin pendingPayments={adminPayments} onApprove={handleAdminApprove} onReject={handleAdminReject} />}
    </Layout>
  );
}