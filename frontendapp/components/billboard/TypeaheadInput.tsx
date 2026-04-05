'use client';

import { useState, useRef, useEffect, useCallback } from 'react';
import './typeahead-input.css';

interface Suggestion {
  id: number;
  title: string;
}

interface TypeaheadInputProps {
  id: string;
  name: string;
  value: string;
  onChange: (value: string) => void;
  fetchSuggestions: (query: string) => Promise<Suggestion[]>;
  placeholder?: string;
  className?: string;
  required?: boolean;
  debounceMs?: number;
  minChars?: number;
}

export function TypeaheadInput({
  id,
  name,
  value,
  onChange,
  fetchSuggestions,
  placeholder,
  className = '',
  required,
  debounceMs = 300,
  minChars = 2,
}: TypeaheadInputProps) {
  const [suggestions, setSuggestions] = useState<Suggestion[]>([]);
  const [isOpen, setIsOpen] = useState(false);
  const [activeIndex, setActiveIndex] = useState(-1);
  const [loading, setLoading] = useState(false);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const fetchData = useCallback(async (query: string) => {
    if (query.length < minChars) {
      setSuggestions([]);
      setIsOpen(false);
      return;
    }
    setLoading(true);
    try {
      const results = await fetchSuggestions(query);
      setSuggestions(results);
      setIsOpen(results.length > 0);
      setActiveIndex(-1);
    } catch {
      setSuggestions([]);
      setIsOpen(false);
    } finally {
      setLoading(false);
    }
  }, [fetchSuggestions, minChars]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = e.target.value;
    onChange(val);

    if (timerRef.current) clearTimeout(timerRef.current);
    timerRef.current = setTimeout(() => fetchData(val), debounceMs);
  };

  const handleSelect = (suggestion: Suggestion) => {
    onChange(suggestion.title);
    setSuggestions([]);
    setIsOpen(false);
    setActiveIndex(-1);
    inputRef.current?.focus();
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (!isOpen) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActiveIndex(prev => (prev < suggestions.length - 1 ? prev + 1 : 0));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActiveIndex(prev => (prev > 0 ? prev - 1 : suggestions.length - 1));
    } else if (e.key === 'Enter' && activeIndex >= 0) {
      e.preventDefault();
      handleSelect(suggestions[activeIndex]);
    } else if (e.key === 'Escape') {
      setIsOpen(false);
      setActiveIndex(-1);
    }
  };

  // Close dropdown on outside click.
  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (wrapperRef.current && !wrapperRef.current.contains(e.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Cleanup timer on unmount.
  useEffect(() => {
    return () => {
      if (timerRef.current) clearTimeout(timerRef.current);
    };
  }, []);

  return (
    <div className="ta-wrapper" ref={wrapperRef}>
      <input
        ref={inputRef}
        type="text"
        id={id}
        name={name}
        value={value}
        onChange={handleInputChange}
        onKeyDown={handleKeyDown}
        onFocus={() => { if (suggestions.length > 0) setIsOpen(true); }}
        placeholder={placeholder}
        className={`${className} ta-input`}
        required={required}
        autoComplete="off"
        role="combobox"
        aria-expanded={isOpen}
        aria-autocomplete="list"
        aria-controls={`${id}-listbox`}
        aria-activedescendant={activeIndex >= 0 ? `${id}-opt-${activeIndex}` : undefined}
      />
      {loading && <span className="ta-spinner" />}
      {isOpen && suggestions.length > 0 && (
        <ul
          id={`${id}-listbox`}
          className="ta-dropdown"
          role="listbox"
        >
          {suggestions.map((s, i) => (
            <li
              key={s.id}
              id={`${id}-opt-${i}`}
              className={`ta-dropdown__item${i === activeIndex ? ' ta-dropdown__item--active' : ''}`}
              role="option"
              aria-selected={i === activeIndex}
              onMouseDown={() => handleSelect(s)}
              onMouseEnter={() => setActiveIndex(i)}
            >
              <span className="ta-dropdown__title">{s.title}</span>
              <span className="ta-dropdown__hint">Existing</span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
