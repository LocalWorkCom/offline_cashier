declare module 'html2canvas' {
  interface Options {
    width?: number;
    height?: number;
    scale?: number;
    useCORS?: boolean;
    allowTaint?: boolean;
    backgroundColor?: string;
    logging?: boolean;
    [key: string]: any;
  }

  function html2canvas(element: HTMLElement, options?: Partial<Options>): Promise<HTMLCanvasElement>;
  export default html2canvas;
}

