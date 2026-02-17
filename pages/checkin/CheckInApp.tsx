import React, { useState, useCallback } from 'react';
import { CheckInMember } from '../../types';
import { lookupMemberByCode } from './checkInData';
import { QRCheckInScreen } from './QRCheckInScreen';
import { MemberWelcomeScreen } from './MemberWelcomeScreen';

export const CheckInApp: React.FC = () => {
  const [member, setMember] = useState<CheckInMember | null>(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState<string | null>(null);

  const handleScan = useCallback(async (code: string) => {
    setMessage(null);
    setLoading(true);
    try {
      const result = await lookupMemberByCode(code);
      if ('member' in result) {
        setMember(result.member);
      } else if (result.error === 'server_error') {
        setMessage('Check-in service unavailable. Try again.');
        setTimeout(() => setMessage(null), 4000);
      } else {
        setMessage('Member not found');
        setTimeout(() => setMessage(null), 3000);
      }
    } finally {
      setLoading(false);
    }
  }, []);

  const handleWelcomeDone = useCallback(() => {
    setMember(null);
  }, []);

  if (member) {
    return <MemberWelcomeScreen member={member} onDone={handleWelcomeDone} />;
  }

  return (
    <QRCheckInScreen
      onScan={handleScan}
      loading={loading}
      message={message}
    />
  );
};
