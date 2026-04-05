'use client';

import { useState, useRef, useEffect } from 'react';
import { aiAPI, type ChatMessage } from '@/lib/api/ai';
import { IconSend } from '@/lib/icons/ui-icons';
import './chatbot.css';

interface Message {
  id: string;
  text: string;
  isBot: boolean;
  timestamp: Date;
}

const SUGGESTIONS = [
  'Find billboards in Dhaka',
  'What are your pricing options?',
  'How do I book a billboard?',
  'Tell me about LED displays',
];

export function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      id: '1',
      text: "Hi! I'm your Billoria assistant. How can I help you find the perfect billboard today?",
      isBot: true,
      timestamp: new Date(),
    },
  ]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  // Auto-scroll to bottom when new messages arrive
  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  // Focus input when chat opens
  useEffect(() => {
    if (isOpen) {
      inputRef.current?.focus();
    }
  }, [isOpen]);

  const sendMessage = async (text: string) => {
    if (!text.trim() || isLoading) return;

    const userMessage: Message = {
      id: Date.now().toString(),
      text: text.trim(),
      isBot: false,
      timestamp: new Date(),
    };

    setMessages(prev => [...prev, userMessage]);
    setInputValue('');
    setIsLoading(true);

    try {
      // Build conversation history for context
      const history: ChatMessage[] = messages.slice(-5).map(msg => ({
        role: msg.isBot ? 'assistant' : 'user',
        content: msg.text,
      }));

      // Call AI API
      const response = await aiAPI.chat(text, { type: 'chatbot' }, history);

      const botMessage: Message = {
        id: (Date.now() + 1).toString(),
        text: response.success
          ? response.message || 'I apologize, I couldn\'t generate a response.'
          : response.error || 'Sorry, I\'m having trouble connecting right now.',
        isBot: true,
        timestamp: new Date(),
      };

      setMessages(prev => [...prev, botMessage]);
    } catch (error) {
      console.error('Chat error:', error);
      const errorMessage: Message = {
        id: (Date.now() + 1).toString(),
        text: 'Sorry, I encountered an error. Please try again.',
        isBot: true,
        timestamp: new Date(),
      };
      setMessages(prev => [...prev, errorMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSendClick = () => {
    sendMessage(inputValue);
  };

  const handleKeyPress = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage(inputValue);
    }
  };

  const handleSuggestionClick = (suggestion: string) => {
    sendMessage(suggestion);
  };

  const formatTime = (date: Date) => {
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  return (
    <div className="chatbot">
      {/* Chat Window */}
      {isOpen && (
        <div className="chatbot__window">
          <div className="chatbot__header">
            <div className="chatbot__header-info">
              <div className="chatbot__avatar">🤖</div>
              <div>
                <h4 className="chatbot__title">Billoria Assistant</h4>
                <span className="chatbot__status">
                  ● {isLoading ? 'Typing...' : 'Online'}
                </span>
              </div>
            </div>
            <button
              onClick={() => setIsOpen(false)}
              className="chatbot__close"
              aria-label="Close chat"
            >
              ✕
            </button>
          </div>

          <div className="chatbot__body">
            {messages.map((msg) => (
              <div
                key={msg.id}
                className={`chatbot__message ${msg.isBot ? 'chatbot__message--bot' : 'chatbot__message--user'
                  }`}
              >
                {msg.isBot && <div className="chatbot__message-avatar">🤖</div>}
                <div className="chatbot__message-bubble">
                  <p>{msg.text}</p>
                  <span className="chatbot__message-time">
                    {formatTime(msg.timestamp)}
                  </span>
                </div>
              </div>
            ))}

            {isLoading && (
              <div className="chatbot__message chatbot__message--bot">
                <div className="chatbot__message-avatar">🤖</div>
                <div className="chatbot__message-bubble chatbot__typing">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>
            )}

            <div ref={messagesEndRef} />

            {messages.length === 1 && (
              <div className="chatbot__suggestions">
                {SUGGESTIONS.map((suggestion, index) => (
                  <button
                    key={index}
                    className="chatbot__suggestion"
                    onClick={() => handleSuggestionClick(suggestion)}
                    disabled={isLoading}
                  >
                    {suggestion}
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="chatbot__footer">
            <input
              ref={inputRef}
              type="text"
              placeholder="Type your message..."
              className="chatbot__input"
              value={inputValue}
              onChange={(e) => setInputValue(e.target.value)}
              onKeyPress={handleKeyPress}
              disabled={isLoading}
            />
            <button
              className="chatbot__send"
              aria-label="Send message"
              onClick={handleSendClick}
              disabled={isLoading || !inputValue.trim()}
            >
              <IconSend />
            </button>
          </div>
        </div>
      )}

      {/* Chat Button */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className={`chatbot__button ${isOpen ? 'chatbot__button--hidden-mobile' : ''}`}
        aria-label="Open chat"
      >
        <span className="chatbot__button-icon">
          {isOpen ? '✕' : '💬'}
        </span>
        {!isOpen && messages.length > 1 && (
          <span className="chatbot__button-badge">{messages.length - 1}</span>
        )}
      </button>
    </div>
  );
}
