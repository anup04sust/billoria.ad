'use client';

import { useState } from 'react';
import './booking-calendar.css';

interface BookingCalendarProps {
  bookedDates?: string[]; // ISO date strings "YYYY-MM-DD"
  bookingMode?: string;
}

const MONTH_NAMES = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];
const DAY_LABELS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

function toISO(d: Date): string {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function formatDisplay(d: Date): string {
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

export function BookingCalendar({ bookedDates = [], bookingMode }: BookingCalendarProps) {
  const todayISO = toISO(new Date());
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const [viewDate, setViewDate] = useState(() => {
    const d = new Date();
    d.setDate(1);
    d.setHours(0, 0, 0, 0);
    return d;
  });

  const [startDate, setStartDate] = useState<Date | null>(null);
  const [endDate, setEndDate] = useState<Date | null>(null);
  const [hoverDate, setHoverDate] = useState<Date | null>(null);

  const bookedSet = new Set(bookedDates);

  const year = viewDate.getFullYear();
  const month = viewDate.getMonth();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  // Shift so Monday = column 0
  const rawFirstDay = new Date(year, month, 1).getDay();
  const startOffset = (rawFirstDay + 6) % 7;

  function prevMonth() {
    setViewDate(d => new Date(d.getFullYear(), d.getMonth() - 1, 1));
  }

  function nextMonth() {
    setViewDate(d => new Date(d.getFullYear(), d.getMonth() + 1, 1));
  }

  function handleDayClick(day: number) {
    const clicked = new Date(year, month, day);
    clicked.setHours(0, 0, 0, 0);
    if (clicked < today) return;
    if (bookedSet.has(toISO(clicked))) return;

    if (!startDate || (startDate && endDate)) {
      setStartDate(clicked);
      setEndDate(null);
    } else {
      if (clicked < startDate) {
        setEndDate(startDate);
        setStartDate(clicked);
      } else {
        setEndDate(clicked);
      }
    }
  }

  function isInRange(d: Date): boolean {
    const end = endDate || hoverDate;
    if (!startDate || !end) return false;
    const lo = startDate < end ? startDate : end;
    const hi = startDate < end ? end : startDate;
    return d > lo && d < hi;
  }

  function isStart(d: Date): boolean {
    return startDate ? toISO(d) === toISO(startDate) : false;
  }

  function isEnd(d: Date): boolean {
    return endDate ? toISO(d) === toISO(endDate) : false;
  }

  function calcDays(): number | null {
    if (!startDate || !endDate) return null;
    return Math.round(Math.abs(endDate.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24));
  }

  const cells: (number | null)[] = [
    ...Array(startOffset).fill(null),
    ...Array.from({ length: daysInMonth }, (_, i) => i + 1),
  ];

  const days = calcDays();

  return (
    <div className="bb-booking-cal">
      <div className="bb-booking-cal__header">
        <span className="bb-booking-cal__title-label">Booking Period</span>
        {bookingMode && <span className="bb-booking-cal__mode">{bookingMode}</span>}
      </div>

      <div className="bb-booking-cal__nav">
        <button
          className="bb-booking-cal__nav-btn"
          onClick={prevMonth}
          aria-label="Previous month"
          type="button"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="15 18 9 12 15 6" />
          </svg>
        </button>
        <span className="bb-booking-cal__month-label">{MONTH_NAMES[month]} {year}</span>
        <button
          className="bb-booking-cal__nav-btn"
          onClick={nextMonth}
          aria-label="Next month"
          type="button"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </button>
      </div>

      <div className="bb-booking-cal__grid" role="grid" aria-label="Booking calendar">
        {DAY_LABELS.map(d => (
          <div key={d} className="bb-booking-cal__day-label" role="columnheader" aria-label={d}>{d}</div>
        ))}
        {cells.map((day, i) => {
          if (day === null) return <div key={`empty-${i}`} aria-hidden="true" />;

          const d = new Date(year, month, day);
          d.setHours(0, 0, 0, 0);
          const iso = toISO(d);
          const isPast = d < today;
          const isBooked = bookedSet.has(iso);
          const isToday = iso === todayISO;
          const inRange = isInRange(d);
          const start = isStart(d);
          const end = isEnd(d);
          const disabled = isPast || isBooked;

          return (
            <button
              key={day}
              className={[
                'bb-booking-cal__day',
                disabled ? 'bb-booking-cal__day--disabled' : '',
                isBooked ? 'bb-booking-cal__day--booked' : '',
                isPast ? 'bb-booking-cal__day--past' : '',
                isToday ? 'bb-booking-cal__day--today' : '',
                inRange ? 'bb-booking-cal__day--range' : '',
                start ? 'bb-booking-cal__day--start' : '',
                end ? 'bb-booking-cal__day--end' : '',
              ].filter(Boolean).join(' ')}
              onClick={() => handleDayClick(day)}
              onMouseEnter={() => {
                if (startDate && !endDate) setHoverDate(new Date(year, month, day));
              }}
              onMouseLeave={() => setHoverDate(null)}
              disabled={disabled}
              type="button"
              aria-label={`${MONTH_NAMES[month]} ${day}${isBooked ? ' — Booked' : isPast ? ' — Past' : ''}`}
              aria-selected={start || end}
              role="gridcell"
            >
              {day}
            </button>
          );
        })}
      </div>

      <div className="bb-booking-cal__summary">
        <div className="bb-booking-cal__summary-row">
          <span className="bb-booking-cal__summary-label">From</span>
          <span className="bb-booking-cal__summary-value">
            {startDate ? formatDisplay(startDate) : 'Select date'}
          </span>
        </div>
        <div className="bb-booking-cal__summary-sep" />
        <div className="bb-booking-cal__summary-row">
          <span className="bb-booking-cal__summary-label">To</span>
          <span className="bb-booking-cal__summary-value">
            {endDate ? formatDisplay(endDate) : 'Select date'}
          </span>
        </div>
      </div>

      {days !== null && (
        <div className="bb-booking-cal__duration">
          {days} day{days !== 1 ? 's' : ''} selected
        </div>
      )}

      <div className="bb-booking-cal__legend">
        <span className="bb-booking-cal__legend-item">
          <span className="bb-booking-cal__legend-dot bb-booking-cal__legend-dot--available" />
          Available
        </span>
        <span className="bb-booking-cal__legend-item">
          <span className="bb-booking-cal__legend-dot bb-booking-cal__legend-dot--booked" />
          Booked
        </span>
        <span className="bb-booking-cal__legend-item">
          <span className="bb-booking-cal__legend-dot bb-booking-cal__legend-dot--selected" />
          Selected
        </span>
      </div>
    </div>
  );
}
