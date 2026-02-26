/**
 * Logger utility that only outputs in development mode.
 * Replaces direct console.log usage throughout the app.
 *
 * Usage:
 *   import { logger } from '@/utils/logger.js'
 *   logger.log('message', data)
 *   logger.warn('warning', data)
 *   logger.error('error', data)
 */
/* eslint-disable no-console */
export const logger = {
    log: (...args) => {
        if (import.meta.env.DEV) console.log(...args)
    },
    warn: (...args) => {
        if (import.meta.env.DEV) console.warn(...args)
    },
    error: (...args) => {
        if (import.meta.env.DEV) console.error(...args)
    },
}
/* eslint-enable no-console */
