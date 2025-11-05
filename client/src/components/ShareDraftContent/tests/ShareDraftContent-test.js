/* global jest, test, expect, afterEach */

import React from 'react';
import mockFetch from 'isomorphic-fetch';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { Component as ShareDraftContent } from '../ShareDraftContent';

jest.mock('isomorphic-fetch');

afterEach(() => {
  mockFetch.mockClear();
});

const mockPopoverField = ({
  children,
  toggleCallback,
  buttonIcon,
  buttonClassName,
  buttonTooltip,
  title,
  data
}) => (
  <div data-testid="popover-field">
    <button
      data-testid="popover-toggle-btn"
      className={buttonClassName}
      title={buttonTooltip}
      data-popover-title={data.popoverTitle}
      data-button-icon={buttonIcon}
      onClick={toggleCallback}
    >
      {title}
    </button>
    <div data-testid="popover-content">{children}</div>
  </div>
);

function makeProps(obj = {}) {
  return {
    id: 'share-draft-content',
    className: 'custom-class',
    button: {
      className: 'btn btn-primary',
      icon: 'share',
      title: 'Share',
      tooltip: 'Share draft content',
    },
    popover: {
      title: 'Share draft content',
    },
    links: {
      generateLink: 'http://example.com/generate',
      learnMore: 'http://example.com/learn-more',
    },
    PopoverField: mockPopoverField,
    ...obj
  };
}

test('ShareDraftContent renders with correct props', () => {
  const { container } = render(<ShareDraftContent {...makeProps()} />);
  const popoverField = screen.getByTestId('popover-field');
  expect(popoverField).not.toBeNull();
  const containerEl = container.querySelector('.share-draft-content__container');
  expect(containerEl).not.toBeNull();
  expect(containerEl.classList.contains('custom-class')).toBe(true);
});

test('ShareDraftContent renders button with correct title', () => {
  render(<ShareDraftContent {...makeProps()} />);
  const button = screen.getByTestId('popover-toggle-btn');
  expect(button.textContent).toBe('Share');
  expect(button.classList.contains('btn')).toBe(true);
  expect(button.classList.contains('btn-primary')).toBe(true);
  expect(button.getAttribute('data-popover-title')).toBe('Share draft content');
});

test('ShareDraftContent renders button with correct icon', () => {
  render(<ShareDraftContent {...makeProps()} />);
  const button = screen.getByTestId('popover-toggle-btn');
  expect(button.getAttribute('data-button-icon')).toBe('share');
});

test('ShareDraftContent renders help text with learnMore link', () => {
  render(<ShareDraftContent {...makeProps()} />);
  const helpText = screen.getByText(/Anyone with this link can view the draft version/);
  expect(helpText).not.toBeNull();
  const learnMoreLink = screen.getByText('Learn more');
  expect(learnMoreLink).not.toBeNull();
  expect(learnMoreLink.getAttribute('href')).toBe('http://example.com/learn-more');
  expect(learnMoreLink.getAttribute('target')).toBe('_blank');
  expect(learnMoreLink.getAttribute('rel')).toBe('noopener noreferrer');
});

test('ShareDraftContent renders help text without learnMore link when not provided', () => {
  render(
    <ShareDraftContent
      {...makeProps({
        links: {
          generateLink: 'http://example.com/generate',
          learnMore: '',
        },
      })}
    />
  );
  expect(screen.getByText(/Anyone with this link can view the draft version/)).not.toBeNull();
  expect(screen.queryByText('Learn more')).toBeNull();
});

test('ShareDraftContent renders input field with initial loading text', () => {
  render(<ShareDraftContent {...makeProps()} />);
  const input = screen.getByRole('textbox');
  expect(input.value).toBe('Loading...');
  expect(input.readOnly).toBe(true);
  expect(input.classList.contains('share-draft-content__link')).toBe(true);
  expect(input.classList.contains('form-control')).toBe(true);
  expect(input.classList.contains('no-change-track')).toBe(true);
});

test('ShareDraftContent calls generateShareDraftLink on first toggle', async () => {
  mockFetch.mockResolvedValueOnce({
    text: () => Promise.resolve('http://example.com/draft?token=abc123'),
  });
  render(<ShareDraftContent {...makeProps()} />);
  const button = screen.getByTestId('popover-toggle-btn');
  fireEvent.click(button);
  await waitFor(() => {
    expect(mockFetch).toHaveBeenCalledWith(
      'http://example.com/generate',
      { credentials: 'same-origin' }
    );
  });
  await waitFor(() => {
    const input = screen.getByRole('textbox');
    expect(input.value).toBe('http://example.com/draft?token=abc123');
  });
});

test('ShareDraftContent does not regenerate link on second toggle', async () => {
  mockFetch.mockResolvedValueOnce({
    text: () => Promise.resolve('http://example.com/draft?token=abc123'),
  });
  render(<ShareDraftContent {...makeProps()} />);
  const button = screen.getByTestId('popover-toggle-btn');
  fireEvent.click(button);
  await waitFor(() => {
    const input = screen.getByRole('textbox');
    expect(input.value).toBe('http://example.com/draft?token=abc123');
  });
  const initialCallCount = mockFetch.mock.calls.length;
  fireEvent.click(button);
  expect(mockFetch.mock.calls.length).toBe(initialCallCount);
});

test('ShareDraftContent displays error message on fetch failure', async () => {
  mockFetch.mockRejectedValueOnce(new Error('Network error'));
  const { container } = render(<ShareDraftContent {...makeProps()} />);
  const button = screen.getByTestId('popover-toggle-btn');
  fireEvent.click(button);
  await waitFor(() => {
    expect(screen.getByText(/There was a problem generating the shareable link/)).not.toBeNull();
  });
  expect(container.querySelector('.alert-danger')).not.toBeNull();
});

test('ShareDraftContent selects link text after successful generation', async () => {
  mockFetch.mockResolvedValueOnce({
    text: () => Promise.resolve('http://example.com/draft?token=xyz789'),
  });
  render(<ShareDraftContent {...makeProps()} />);
  const input = screen.getByRole('textbox');
  input.select = jest.fn();
  const button = screen.getByTestId('popover-toggle-btn');
  fireEvent.click(button);
  await waitFor(() => {
    expect(input.select).toHaveBeenCalled();
  });
});

test('ShareDraftContent handles input change without errors', () => {
  render(<ShareDraftContent {...makeProps()} />);
  const input = screen.getByRole('textbox');
  expect(() => {
    fireEvent.change(input, { target: { value: 'new value' } });
  }).not.toThrow();
  expect(input.value).toBe('Loading...');
});

test('ShareDraftContent renders external link icon with accessibility text', () => {
  const { container } = render(<ShareDraftContent {...makeProps()} />);
  const externalLinkIcon = container.querySelector('.share-draft-content__learn-more__icon');
  expect(externalLinkIcon).not.toBeNull();
  expect(externalLinkIcon.classList.contains('font-icon-external-link')).toBe(true);
  expect(externalLinkIcon.getAttribute('aria-hidden')).toBe('true');
  const accessibilityText = screen.getByText('(external link)');
  expect(accessibilityText).not.toBeNull();
  expect(accessibilityText.classList.contains('visually-hidden')).toBe(true);
});

test('ShareDraftContent with no className does not add custom class', () => {
  const { container } = render(
    <ShareDraftContent
      {...makeProps({
        className: undefined,
      })}
    />
  );
  expect(container.querySelector('.share-draft-content__container')).not.toBeNull();
});

test('ShareDraftContent does not render learnMore link when link not present in props', () => {
  render(
    <ShareDraftContent
      {...makeProps({
        links: {
          generateLink: 'http://example.com/generate',
        },
      })}
    />
  );
  expect(screen.queryByText('Learn more')).toBeNull();
});
