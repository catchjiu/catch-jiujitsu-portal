import { ClassSession, Payment, User } from './types';

export const currentUser: User = {
  id: 1,
  name: "Alex 'The Shark'",
  email: "alex@catchbjj.com",
  rank: "Blue",
  stripes: 2,
  matHours: 142,
  isAdmin: false, 
  avatarUrl: "https://picsum.photos/200"
};

const today = new Date();
const tomorrow = new Date(today);
tomorrow.setDate(tomorrow.getDate() + 1);

export const mockClasses: ClassSession[] = [
  {
    id: 101,
    title: "Morning Fundamentals",
    type: "Fundamentals",
    startTime: new Date(today.setHours(7, 0, 0, 0)).toISOString(),
    durationMinutes: 60,
    instructor: "Prof. Marco",
    capacity: 20,
    bookedCount: 18,
    isBookedByUser: false,
  },
  {
    id: 102,
    title: "Advanced No-Gi",
    type: "No-Gi",
    startTime: new Date(today.setHours(18, 0, 0, 0)).toISOString(),
    durationMinutes: 90,
    instructor: "Coach Sarah",
    capacity: 25,
    bookedCount: 12,
    isBookedByUser: true,
  },
  {
    id: 103,
    title: "Open Mat",
    type: "Open Mat",
    startTime: new Date(today.setHours(19, 30, 0, 0)).toISOString(),
    durationMinutes: 120,
    instructor: "Various",
    capacity: 40,
    bookedCount: 5,
    isBookedByUser: false,
  },
  {
    id: 104,
    title: "Competition Gi",
    type: "Gi",
    startTime: new Date(tomorrow.setHours(18, 0, 0, 0)).toISOString(),
    durationMinutes: 90,
    instructor: "Prof. Marco",
    capacity: 20,
    bookedCount: 20, // Full
    isBookedByUser: false,
  }
];

export const mockPayments: Payment[] = [
  {
    id: 501,
    amount: 1500, // Currency usually handled in logic, assumed THB or local currency
    month: "October 2023",
    status: "Paid",
    submittedAt: "2023-10-02T10:00:00Z"
  },
  {
    id: 502,
    amount: 1500,
    month: "November 2023",
    status: "Overdue",
  }
];

export const mockAdminPayments: Payment[] = [
  {
    id: 601,
    amount: 1500,
    month: "November 2023",
    status: "Pending Verification",
    proofImageUrl: "https://picsum.photos/400/600",
    submittedAt: "2023-11-01T14:30:00Z"
  }
];