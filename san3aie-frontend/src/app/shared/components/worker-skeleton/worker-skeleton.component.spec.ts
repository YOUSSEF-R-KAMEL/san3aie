import { ComponentFixture, TestBed } from '@angular/core/testing';

import { WorkerSkeletonComponent } from './worker-skeleton.component';

describe('WorkerSkeletonComponent', () => {
  let component: WorkerSkeletonComponent;
  let fixture: ComponentFixture<WorkerSkeletonComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [WorkerSkeletonComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(WorkerSkeletonComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
