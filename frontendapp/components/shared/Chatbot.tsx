'use client';

import { useState } from 'react';
import './chatbot.css';

export function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);

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
                <span className="chatbot__status">● Online</span>
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
            <div className="chatbot__message chatbot__message--bot">
              <div className="chatbot__message-avatar">🤖</div>
              <div className="chatbot__message-bubble">
                <p>Hi! I'm your Billoria assistant. How can I help you today?</p>
                <span className="chatbot__message-time">Just now</span>
              </div>
            </div>

            <div className="chatbot__suggestions">
              <button className="chatbot__suggestion">Find billboards</button>
              <button className="chatbot__suggestion">Pricing info</button>
              <button className="chatbot__suggestion">Contact support</button>
            </div>
          </div>

          <div className="chatbot__footer">
            <input
              type="text"
              placeholder="Type your message..."
              className="chatbot__input"
            />
            <button className="chatbot__send" aria-label="Send message">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" />
              </svg>
            </button>
          </div>
        </div>
      )}

      {/* Chat Button */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="chatbot__button"
        aria-label="Open chat"
      >
        <span className="chatbot__button-icon">
          {isOpen ? '✕' : '💬'}
        </span>
        <span className="chatbot__button-badge">1</span>
      </button>
    </div>
  );
}
