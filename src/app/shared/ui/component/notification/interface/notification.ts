 export interface notification {
  id: number;
  description: string;
  notification_type: string;
  title: string;
  is_read: boolean;
  statusId: number;
  date_time: any;
}
export interface notifications {
  date:string;
  notifications:notification[]
}
