import { describe, expect, test, beforeEach } from 'vitest'
import { trapTabKey } from '../focusTrap'

function keydown(key: string, shiftKey: boolean): KeyboardEvent {
  return new KeyboardEvent('keydown', { key, shiftKey, bubbles: true, cancelable: true })
}

describe('trapTabKey', () => {
  let container: HTMLDivElement
  let first: HTMLButtonElement
  let middle: HTMLButtonElement
  let last: HTMLButtonElement

  // Arrange (shared)
  beforeEach(() => {
    container = document.createElement('div')
    first = document.createElement('button')
    middle = document.createElement('button')
    last = document.createElement('button')
    container.append(first, middle, last)
    document.body.appendChild(container)
    return () => container.remove()
  })

  test('Tab on last wraps focus to first', () => {
    // Arrange
    last.focus()
    const event = keydown('Tab', false)

    // Act
    trapTabKey(container, event)

    // Assert
    expect(event.defaultPrevented).toBe(true)
    expect(document.activeElement).toBe(first)
  })

  test('Shift+Tab on first wraps focus to last', () => {
    // Arrange
    first.focus()
    const event = keydown('Tab', true)

    // Act
    trapTabKey(container, event)

    // Assert
    expect(event.defaultPrevented).toBe(true)
    expect(document.activeElement).toBe(last)
  })

  test('Tab mid-list passes through untouched', () => {
    // Arrange
    middle.focus()
    const event = keydown('Tab', false)

    // Act
    trapTabKey(container, event)

    // Assert
    expect(event.defaultPrevented).toBe(false)
    expect(document.activeElement).toBe(middle)
  })

  test('container with zero focusables does not throw', () => {
    // Arrange
    const empty = document.createElement('div')
    document.body.appendChild(empty)

    // Act + Assert
    expect(() => trapTabKey(empty, keydown('Tab', false))).not.toThrow()
    empty.remove()
  })
})
