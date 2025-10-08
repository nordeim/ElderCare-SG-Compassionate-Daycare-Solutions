import { test, expect } from '@playwright/test';

test('homepage has correct title', async ({ page }) => {
  await page.goto('/');
  
  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/ElderCare SG/);
});

test('navigation works correctly', async ({ page }) => {
  await page.goto('/');
  
  // Click the Programs link
  await page.click('text=Programs');
  
  // Expects the URL to contain programs
  await expect(page).toHaveURL(/.*programs/);
});

test('Book Visit button is visible', async ({ page }) => {
  await page.goto('/');
  
  // Check if the Book Visit button is visible
  await expect(page.locator('text=Book Visit')).toBeVisible();
});
