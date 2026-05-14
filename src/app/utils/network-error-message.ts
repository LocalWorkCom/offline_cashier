/** بديل عربي لرسالة المتصفح الشائعة عند فشل الطلب */
export const NETWORK_SERVER_ERROR_AR =
  'حدث خطا من السيرفر يرجى المحاولة مره اخرى';

/** يستبدل نص Failed to fetch (بأي حالة أحرف) بنفس المعنى بالعربية */
export function replaceFailedToFetchMessage(
  text: string | null | undefined
): string {
  if (text == null || text === '') {
    return text ?? '';
  }
  if (/failed to fetch/i.test(text)) {
    return NETWORK_SERVER_ERROR_AR;
  }
  return text;
}
